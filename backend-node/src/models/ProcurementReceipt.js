const mongoose = require('mongoose');

/**
 * ProcurementReceipt Schema
 * Merepresentasikan catatan penerimaan barang hasil pengadaan.
 * Barang yang diterima bisa sebagian (partial delivery) dari total yang dipesan.
 * Dicatat oleh staf_admin setelah barang tiba.
 */
const procurementReceiptSchema = new mongoose.Schema(
  {
    draft_item_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'ProcurementDraftItem',
      required: [true, 'ID item draft wajib diisi'],
    },
    received_date: {
      type: Date,
      required: [true, 'Tanggal penerimaan wajib diisi'],
      default: Date.now,
    },
    quantity: {
      type: Number,
      required: [true, 'Jumlah barang yang diterima wajib diisi'],
      min: [1, 'Jumlah yang diterima minimal 1'],
      validate: {
        validator: Number.isInteger,
        message: 'Jumlah yang diterima harus bilangan bulat',
      },
    },
    staf_admin_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: [true, 'ID staf admin yang menerima wajib diisi'],
    },
    notes: {
      type: String,
      trim: true,
      maxlength: [500, 'Catatan tidak boleh lebih dari 500 karakter'],
      default: null,
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: false },
    versionKey: false,
  }
);

// --- Indexes ---
procurementReceiptSchema.index({ draft_item_id: 1 });
procurementReceiptSchema.index({ staf_admin_id: 1 });
procurementReceiptSchema.index({ received_date: 1 });
procurementReceiptSchema.index({ draft_item_id: 1, received_date: -1 });

// --- Virtual Relationships ---

/**
 * Mendapatkan semua inventaris yang dibuat dari receipt ini.
 */
procurementReceiptSchema.virtual('inventories', {
  ref: 'Inventory',
  localField: '_id',
  foreignField: 'procurement_receipt_id',
});

// --- Static Methods ---

/**
 * Menghitung total jumlah yang sudah diterima untuk suatu item pengadaan.
 * @param {ObjectId} draftItemId
 * @returns {Promise<number>}
 */
procurementReceiptSchema.statics.getTotalReceivedByItemId = async function (draftItemId) {
  const result = await this.aggregate([
    {
      $match: { draft_item_id: new mongoose.Types.ObjectId(draftItemId) },
    },
    {
      $group: {
        _id: null,
        total: { $sum: '$quantity' },
      },
    },
  ]);
  return result.length > 0 ? result[0].total : 0;
};

/**
 * Mendapatkan riwayat penerimaan barang berdasarkan item draft.
 * @param {ObjectId} draftItemId
 * @returns {Promise<ProcurementReceipt[]>}
 */
procurementReceiptSchema.statics.findByDraftItemId = function (draftItemId) {
  return this.find({ draft_item_id: draftItemId })
    .populate('staf_admin_id', 'name email')
    .sort({ received_date: -1 });
};

const ProcurementReceipt = mongoose.model(
  'ProcurementReceipt',
  procurementReceiptSchema,
  'procurement_receipts'
);

module.exports = ProcurementReceipt;
