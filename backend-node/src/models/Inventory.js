const mongoose = require('mongoose');

/**
 * Inventory Schema
 * Merepresentasikan aset/inventaris tetap laboratorium (non-consumable).
 * Setiap inventaris memiliki kode label unik dan QR code untuk identifikasi.
 *
 * Kondisi aset:
 *  - baik              : Aset dalam kondisi baik
 *  - perlu maintenance : Aset memerlukan perawatan
 *  - rusak             : Aset dalam kondisi rusak
 *  - tidak layak pakai : Aset sudah tidak bisa digunakan
 *
 * Status aset:
 *  - active      : Aktif digunakan
 *  - maintenance : Sedang dalam proses maintenance
 *  - retired     : Sudah dinonaktifkan/pensiunkan
 *  - lost        : Hilang
 */
const inventorySchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: [true, 'Nama inventaris wajib diisi'],
      trim: true,
      maxlength: [200, 'Nama inventaris tidak boleh lebih dari 200 karakter'],
    },
    description: {
      type: String,
      trim: true,
      maxlength: [1000, 'Deskripsi tidak boleh lebih dari 1000 karakter'],
      default: null,
    },
    price: {
      type: Number,
      required: [true, 'Harga inventaris wajib diisi'],
      min: [0, 'Harga tidak boleh negatif'],
    },
    condition: {
      type: String,
      required: [true, 'Kondisi aset wajib diisi'],
      enum: {
        values: ['baik', 'perlu maintenance', 'rusak', 'tidak layak pakai'],
        message: 'Kondisi tidak valid. Pilih: baik, perlu maintenance, rusak, tidak layak pakai',
      },
      default: 'baik',
    },
    label_code: {
      type: String,
      required: [true, 'Kode label aset wajib diisi'],
      unique: true,
      trim: true,
      uppercase: true,
      match: [/^[A-Z]+\/\d{4}\/\d{4}$/, 'Format kode label tidak valid (contoh: AST/2026/0001)'],
    },
    qr_code: {
      type: String,
      trim: true,
      default: null,
    },
    room_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Room',
      required: [true, 'ID ruangan wajib diisi'],
    },
    status: {
      type: String,
      required: [true, 'Status aset wajib diisi'],
      enum: {
        values: ['active', 'maintenance', 'retired', 'lost'],
        message: 'Status tidak valid. Pilih: active, maintenance, retired, lost',
      },
      default: 'active',
    },
    /**
     * Referensi ke procurement receipt jika inventaris ini
     * berasal dari proses pengadaan.
     */
    procurement_receipt_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'ProcurementReceipt',
      default: null,
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
inventorySchema.index({ label_code: 1 }, { unique: true });
inventorySchema.index({ room_id: 1 });
inventorySchema.index({ status: 1 });
inventorySchema.index({ condition: 1 });
inventorySchema.index({ procurement_receipt_id: 1 });
inventorySchema.index({ room_id: 1, status: 1 });
inventorySchema.index({ name: 'text', description: 'text' }); // Full-text search

// --- Virtual Relationships ---

/**
 * Mendapatkan semua log maintenance untuk inventaris ini.
 */
inventorySchema.virtual('maintenance_logs', {
  ref: 'InventoryMaintenanceLog',
  localField: '_id',
  foreignField: 'inventory_item_id',
});

// --- Virtual Fields ---

/**
 * Mengembalikan true jika aset sedang dalam kondisi memerlukan maintenance.
 */
inventorySchema.virtual('needs_maintenance').get(function () {
  return this.condition === 'perlu maintenance' || this.status === 'maintenance';
});

/**
 * Mengembalikan true jika aset masih aktif.
 */
inventorySchema.virtual('is_active').get(function () {
  return this.status === 'active';
});

// --- Middleware (Hooks) ---

/**
 * Otomatis mengubah status menjadi 'maintenance' jika kondisi berubah
 * menjadi 'perlu maintenance'.
 */
inventorySchema.pre('save', function (next) {
  if (this.isModified('condition') && this.condition === 'perlu maintenance') {
    this.status = 'maintenance';
  }
  next();
});

// --- Instance Methods ---

/**
 * Mengubah status aset menjadi maintenance.
 * @returns {Promise<Inventory>}
 */
inventorySchema.methods.markAsMaintenance = function () {
  this.status = 'maintenance';
  this.condition = 'perlu maintenance';
  return this.save();
};

/**
 * Mengubah status aset menjadi aktif setelah maintenance selesai.
 * @param {string} newCondition - Kondisi aset setelah maintenance
 * @returns {Promise<Inventory>}
 */
inventorySchema.methods.markAsActive = function (newCondition = 'baik') {
  this.status = 'active';
  this.condition = newCondition;
  return this.save();
};

/**
 * Menonaktifkan/memensiunkan aset.
 * @returns {Promise<Inventory>}
 */
inventorySchema.methods.retire = function () {
  this.status = 'retired';
  return this.save();
};

// --- Static Methods ---

/**
 * Mencari inventaris berdasarkan kode label.
 * @param {string} labelCode
 * @returns {Promise<Inventory|null>}
 */
inventorySchema.statics.findByLabelCode = function (labelCode) {
  return this.findOne({ label_code: labelCode.toUpperCase() });
};

/**
 * Mendapatkan statistik kondisi aset per ruangan.
 * @param {ObjectId} roomId
 * @returns {Promise<Object[]>}
 */
inventorySchema.statics.getConditionStatsByRoom = function (roomId) {
  const match = roomId ? { $match: { room_id: new mongoose.Types.ObjectId(roomId) } } : { $match: {} };
  return this.aggregate([
    match,
    {
      $group: {
        _id: '$condition',
        count: { $sum: 1 },
      },
    },
    { $sort: { _id: 1 } },
  ]);
};

/**
 * Generate kode label aset berikutnya untuk tahun tertentu.
 * Format: AST/YYYY/XXXX
 * @param {number} year - Tahun pengadaan
 * @returns {Promise<string>}
 */
inventorySchema.statics.generateNextLabelCode = async function (year) {
  const yearStr = year.toString();
  const lastItem = await this.findOne({
    label_code: { $regex: `^AST/${yearStr}/` },
  })
    .sort({ label_code: -1 })
    .select('label_code');

  let nextNumber = 1;
  if (lastItem) {
    const parts = lastItem.label_code.split('/');
    nextNumber = parseInt(parts[2], 10) + 1;
  }

  const paddedNumber = nextNumber.toString().padStart(4, '0');
  return `AST/${yearStr}/${paddedNumber}`;
};

const Inventory = mongoose.model('Inventory', inventorySchema, 'inventories');

module.exports = Inventory;
