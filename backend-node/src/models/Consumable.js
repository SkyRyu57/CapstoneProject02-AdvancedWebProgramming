const mongoose = require('mongoose');

/**
 * Consumable Schema
 * Merepresentasikan bahan/barang habis pakai di laboratorium.
 * Berbeda dengan inventory, consumable memiliki stok yang berkurang saat digunakan.
 * Contoh: Kabel UTP Cat6, RJ45 Connector, Thermal Paste, Label Aset.
 *
 * Sistem akan memberikan peringatan ketika stok di bawah min_stock.
 */
const consumableSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: [true, 'Nama consumable wajib diisi'],
      trim: true,
      maxlength: [200, 'Nama consumable tidak boleh lebih dari 200 karakter'],
    },
    description: {
      type: String,
      trim: true,
      maxlength: [500, 'Deskripsi tidak boleh lebih dari 500 karakter'],
      default: null,
    },
    unit: {
      type: String,
      required: [true, 'Satuan unit wajib diisi'],
      trim: true,
      maxlength: [50, 'Satuan unit tidak boleh lebih dari 50 karakter'],
    },
    stock: {
      type: Number,
      required: [true, 'Stok wajib diisi'],
      min: [0, 'Stok tidak boleh negatif'],
      default: 0,
      validate: {
        validator: Number.isInteger,
        message: 'Stok harus bilangan bulat',
      },
    },
    min_stock: {
      type: Number,
      required: [true, 'Stok minimum wajib diisi'],
      min: [0, 'Stok minimum tidak boleh negatif'],
      default: 0,
      validate: {
        validator: Number.isInteger,
        message: 'Stok minimum harus bilangan bulat',
      },
    },
    room_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Room',
      required: [true, 'ID ruangan wajib diisi'],
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
consumableSchema.index({ room_id: 1 });
consumableSchema.index({ stock: 1 });
consumableSchema.index({ name: 'text', description: 'text' }); // Full-text search

// --- Virtual Relationships ---

/**
 * Mendapatkan semua penyesuaian stok untuk consumable ini.
 */
consumableSchema.virtual('stock_adjustments', {
  ref: 'ConsumableStockAdjustment',
  localField: '_id',
  foreignField: 'consumable_id',
});

// --- Virtual Fields ---

/**
 * Mengembalikan true jika stok di bawah batas minimum.
 */
consumableSchema.virtual('is_low_stock').get(function () {
  return this.stock < this.min_stock;
});

/**
 * Mengembalikan true jika stok habis.
 */
consumableSchema.virtual('is_out_of_stock').get(function () {
  return this.stock <= 0;
});

/**
 * Menghitung selisih stok dengan stok minimum.
 * Nilai negatif berarti kekurangan stok.
 */
consumableSchema.virtual('stock_deficit').get(function () {
  return this.stock - this.min_stock;
});

// --- Instance Methods ---

/**
 * Menambah stok consumable.
 * @param {number} amount - Jumlah yang ditambahkan
 * @returns {Promise<Consumable>}
 */
consumableSchema.methods.addStock = function (amount) {
  if (!Number.isInteger(amount) || amount <= 0) {
    throw new Error('Jumlah penambahan stok harus bilangan bulat positif');
  }
  this.stock += amount;
  return this.save();
};

/**
 * Mengurangi stok consumable.
 * @param {number} amount - Jumlah yang dikurangi
 * @returns {Promise<Consumable>}
 */
consumableSchema.methods.reduceStock = function (amount) {
  if (!Number.isInteger(amount) || amount <= 0) {
    throw new Error('Jumlah pengurangan stok harus bilangan bulat positif');
  }
  if (this.stock < amount) {
    throw new Error(`Stok tidak mencukupi. Stok tersedia: ${this.stock} ${this.unit}`);
  }
  this.stock -= amount;
  return this.save();
};

// --- Static Methods ---

/**
 * Mendapatkan semua consumable dengan stok di bawah minimum.
 * @returns {Promise<Consumable[]>}
 */
consumableSchema.statics.findLowStock = function () {
  return this.aggregate([
    {
      $match: {
        $expr: { $lt: ['$stock', '$min_stock'] },
      },
    },
    { $sort: { stock: 1 } },
  ]);
};

/**
 * Mendapatkan ringkasan stok per ruangan.
 * @returns {Promise<Object[]>}
 */
consumableSchema.statics.getStockSummaryByRoom = function () {
  return this.aggregate([
    {
      $group: {
        _id: '$room_id',
        total_items: { $sum: 1 },
        low_stock_items: {
          $sum: {
            $cond: [{ $lt: ['$stock', '$min_stock'] }, 1, 0],
          },
        },
      },
    },
    {
      $lookup: {
        from: 'rooms',
        localField: '_id',
        foreignField: '_id',
        as: 'room',
      },
    },
    { $unwind: '$room' },
    {
      $project: {
        room_name: '$room.name',
        total_items: 1,
        low_stock_items: 1,
      },
    },
  ]);
};

const Consumable = mongoose.model('Consumable', consumableSchema, 'consumables');

module.exports = Consumable;
