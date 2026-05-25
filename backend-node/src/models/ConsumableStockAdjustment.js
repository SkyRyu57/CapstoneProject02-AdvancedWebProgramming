const mongoose = require('mongoose');

/**
 * ConsumableStockAdjustment Schema
 * Merepresentasikan catatan perubahan stok consumable.
 * Setiap perubahan stok (masuk/keluar) dicatat di sini untuk audit trail.
 *
 * Tipe referensi:
 *  - procurement_receipt : Penambahan stok dari penerimaan pengadaan
 *  - maintenance         : Pengurangan stok karena digunakan saat maintenance
 *  - manual              : Penyesuaian manual oleh admin/staf
 *
 * Nilai quantity_change:
 *  - Positif : Penambahan stok (barang masuk)
 *  - Negatif : Pengurangan stok (barang keluar/terpakai)
 */
const consumableStockAdjustmentSchema = new mongoose.Schema(
  {
    consumable_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Consumable',
      required: [true, 'ID consumable wajib diisi'],
    },
    quantity_change: {
      type: Number,
      required: [true, 'Perubahan jumlah stok wajib diisi'],
      validate: [
        {
          validator: Number.isInteger,
          message: 'Perubahan stok harus bilangan bulat',
        },
        {
          validator: (val) => val !== 0,
          message: 'Perubahan stok tidak boleh nol',
        },
      ],
    },
    reason: {
      type: String,
      required: [true, 'Alasan perubahan stok wajib diisi'],
      trim: true,
      maxlength: [500, 'Alasan tidak boleh lebih dari 500 karakter'],
    },
    reference_type: {
      type: String,
      required: [true, 'Tipe referensi wajib diisi'],
      enum: {
        values: ['procurement_receipt', 'maintenance', 'manual'],
        message: 'Tipe referensi tidak valid. Pilih: procurement_receipt, maintenance, manual',
      },
    },
    reference_id: {
      type: mongoose.Schema.Types.ObjectId,
      default: null,
    },
    created_by: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: [true, 'ID pembuat catatan wajib diisi'],
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: false },
    versionKey: false,
  }
);

// --- Indexes ---
consumableStockAdjustmentSchema.index({ consumable_id: 1 });
consumableStockAdjustmentSchema.index({ created_by: 1 });
consumableStockAdjustmentSchema.index({ reference_type: 1, reference_id: 1 });
consumableStockAdjustmentSchema.index({ consumable_id: 1, created_at: -1 });

// --- Virtual Fields ---

/**
 * Mengembalikan true jika ini adalah penambahan stok (incoming).
 */
consumableStockAdjustmentSchema.virtual('is_incoming').get(function () {
  return this.quantity_change > 0;
});

/**
 * Mengembalikan true jika ini adalah pengurangan stok (outgoing).
 */
consumableStockAdjustmentSchema.virtual('is_outgoing').get(function () {
  return this.quantity_change < 0;
});

/**
 * Mengembalikan nilai absolut dari perubahan stok.
 */
consumableStockAdjustmentSchema.virtual('absolute_change').get(function () {
  return Math.abs(this.quantity_change);
});

// --- Static Methods ---

/**
 * Mendapatkan riwayat perubahan stok untuk suatu consumable.
 * @param {ObjectId} consumableId
 * @param {number} limit - Batas jumlah record (default: 50)
 * @returns {Promise<ConsumableStockAdjustment[]>}
 */
consumableStockAdjustmentSchema.statics.findByConsumableId = function (consumableId, limit = 50) {
  return this.find({ consumable_id: consumableId })
    .populate('created_by', 'name role')
    .sort({ created_at: -1 })
    .limit(limit);
};

/**
 * Mendapatkan total pemasukan stok untuk suatu consumable dalam rentang tanggal.
 * @param {ObjectId} consumableId
 * @param {Date} startDate
 * @param {Date} endDate
 * @returns {Promise<number>}
 */
consumableStockAdjustmentSchema.statics.getTotalIncoming = async function (
  consumableId,
  startDate,
  endDate
) {
  const match = {
    consumable_id: new mongoose.Types.ObjectId(consumableId),
    quantity_change: { $gt: 0 },
  };
  if (startDate || endDate) {
    match.created_at = {};
    if (startDate) match.created_at.$gte = startDate;
    if (endDate) match.created_at.$lte = endDate;
  }

  const result = await this.aggregate([
    { $match: match },
    { $group: { _id: null, total: { $sum: '$quantity_change' } } },
  ]);
  return result.length > 0 ? result[0].total : 0;
};

/**
 * Mendapatkan total pengeluaran stok untuk suatu consumable dalam rentang tanggal.
 * @param {ObjectId} consumableId
 * @param {Date} startDate
 * @param {Date} endDate
 * @returns {Promise<number>}
 */
consumableStockAdjustmentSchema.statics.getTotalOutgoing = async function (
  consumableId,
  startDate,
  endDate
) {
  const match = {
    consumable_id: new mongoose.Types.ObjectId(consumableId),
    quantity_change: { $lt: 0 },
  };
  if (startDate || endDate) {
    match.created_at = {};
    if (startDate) match.created_at.$gte = startDate;
    if (endDate) match.created_at.$lte = endDate;
  }

  const result = await this.aggregate([
    { $match: match },
    { $group: { _id: null, total: { $sum: '$quantity_change' } } },
  ]);
  return result.length > 0 ? Math.abs(result[0].total) : 0;
};

const ConsumableStockAdjustment = mongoose.model(
  'ConsumableStockAdjustment',
  consumableStockAdjustmentSchema,
  'consumable_stock_adjustments'
);

module.exports = ConsumableStockAdjustment;
