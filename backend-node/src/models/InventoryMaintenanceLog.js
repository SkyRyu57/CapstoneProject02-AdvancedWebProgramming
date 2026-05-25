const mongoose = require('mongoose');

/**
 * InventoryMaintenanceLog Schema
 * Merepresentasikan catatan log pemeliharaan/maintenance inventaris.
 * Dicatat oleh staf_lab setelah melakukan maintenance pada aset.
 *
 * Setiap log dapat menyertakan daftar consumable yang digunakan
 * selama proses maintenance (via relasi ke MaintenanceConsumableUsage).
 */
const inventoryMaintenanceLogSchema = new mongoose.Schema(
  {
    inventory_item_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Inventory',
      required: [true, 'ID inventaris yang dimaintenance wajib diisi'],
    },
    staf_lab_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: [true, 'ID staf lab yang melakukan maintenance wajib diisi'],
    },
    maintenance_date: {
      type: Date,
      required: [true, 'Tanggal maintenance wajib diisi'],
      default: Date.now,
    },
    description: {
      type: String,
      required: [true, 'Deskripsi kegiatan maintenance wajib diisi'],
      trim: true,
      maxlength: [2000, 'Deskripsi tidak boleh lebih dari 2000 karakter'],
    },
    condition_before: {
      type: String,
      required: [true, 'Kondisi aset sebelum maintenance wajib diisi'],
      enum: {
        values: ['baik', 'perlu maintenance', 'rusak', 'tidak layak pakai'],
        message: 'Kondisi tidak valid',
      },
    },
    condition_after: {
      type: String,
      required: [true, 'Kondisi aset setelah maintenance wajib diisi'],
      enum: {
        values: ['baik', 'perlu maintenance', 'rusak', 'tidak layak pakai'],
        message: 'Kondisi tidak valid',
      },
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: false },
    versionKey: false,
  }
);

// --- Indexes ---
inventoryMaintenanceLogSchema.index({ inventory_item_id: 1 });
inventoryMaintenanceLogSchema.index({ staf_lab_id: 1 });
inventoryMaintenanceLogSchema.index({ maintenance_date: 1 });
inventoryMaintenanceLogSchema.index({ inventory_item_id: 1, maintenance_date: -1 });

// --- Virtual Relationships ---

/**
 * Mendapatkan semua consumable yang digunakan dalam maintenance ini.
 */
inventoryMaintenanceLogSchema.virtual('consumable_usages', {
  ref: 'MaintenanceConsumableUsage',
  localField: '_id',
  foreignField: 'maintenance_log_id',
});

// --- Virtual Fields ---

/**
 * Mengembalikan true jika kondisi aset membaik setelah maintenance.
 */
inventoryMaintenanceLogSchema.virtual('is_improved').get(function () {
  const conditionOrder = ['tidak layak pakai', 'rusak', 'perlu maintenance', 'baik'];
  return (
    conditionOrder.indexOf(this.condition_after) >
    conditionOrder.indexOf(this.condition_before)
  );
});

// --- Static Methods ---

/**
 * Mendapatkan riwayat maintenance untuk suatu inventaris.
 * @param {ObjectId} inventoryItemId
 * @param {number} limit - Batas jumlah record (default: 20)
 * @returns {Promise<InventoryMaintenanceLog[]>}
 */
inventoryMaintenanceLogSchema.statics.findByInventoryId = function (inventoryItemId, limit = 20) {
  return this.find({ inventory_item_id: inventoryItemId })
    .populate('staf_lab_id', 'name email')
    .sort({ maintenance_date: -1 })
    .limit(limit);
};

/**
 * Mendapatkan statistik maintenance per staf lab dalam rentang waktu.
 * @param {Date} startDate
 * @param {Date} endDate
 * @returns {Promise<Object[]>}
 */
inventoryMaintenanceLogSchema.statics.getStatsByStaff = function (startDate, endDate) {
  const match = {};
  if (startDate || endDate) {
    match.maintenance_date = {};
    if (startDate) match.maintenance_date.$gte = startDate;
    if (endDate) match.maintenance_date.$lte = endDate;
  }

  return this.aggregate([
    { $match: match },
    {
      $group: {
        _id: '$staf_lab_id',
        total_maintenance: { $sum: 1 },
        last_maintenance: { $max: '$maintenance_date' },
      },
    },
    {
      $lookup: {
        from: 'users',
        localField: '_id',
        foreignField: '_id',
        as: 'staf',
      },
    },
    { $unwind: '$staf' },
    {
      $project: {
        staf_name: '$staf.name',
        staf_email: '$staf.email',
        total_maintenance: 1,
        last_maintenance: 1,
      },
    },
    { $sort: { total_maintenance: -1 } },
  ]);
};

/**
 * Mendapatkan jumlah maintenance yang dilakukan dalam suatu bulan.
 * @param {number} year
 * @param {number} month - 1-12
 * @returns {Promise<number>}
 */
inventoryMaintenanceLogSchema.statics.countByMonth = function (year, month) {
  const startDate = new Date(year, month - 1, 1);
  const endDate = new Date(year, month, 0, 23, 59, 59);
  return this.countDocuments({
    maintenance_date: { $gte: startDate, $lte: endDate },
  });
};

const InventoryMaintenanceLog = mongoose.model(
  'InventoryMaintenanceLog',
  inventoryMaintenanceLogSchema,
  'inventory_maintenance_logs'
);

module.exports = InventoryMaintenanceLog;
