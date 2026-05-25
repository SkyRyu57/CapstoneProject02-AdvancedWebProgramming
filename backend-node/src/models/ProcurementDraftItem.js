const mongoose = require('mongoose');

/**
 * ProcurementDraftItem Schema
 * Merepresentasikan item/barang yang ada dalam suatu draft pengadaan.
 * Setiap item bisa berupa inventory (aset tetap) atau consumable (bahan habis pakai).
 *
 * Status persetujuan item:
 *  - pending  : Belum direview
 *  - approved : Disetujui untuk pengadaan
 *  - rejected : Ditolak
 */
const procurementDraftItemSchema = new mongoose.Schema(
  {
    draft_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'ProcurementDraft',
      required: [true, 'ID draft pengadaan wajib diisi'],
    },
    item_type: {
      type: String,
      required: [true, 'Tipe item wajib diisi'],
      enum: {
        values: ['inventory', 'consumable'],
        message: 'Tipe item tidak valid. Pilih: inventory atau consumable',
      },
    },
    name: {
      type: String,
      required: [true, 'Nama item wajib diisi'],
      trim: true,
      maxlength: [200, 'Nama item tidak boleh lebih dari 200 karakter'],
    },
    price: {
      type: Number,
      required: [true, 'Harga item wajib diisi'],
      min: [0, 'Harga tidak boleh negatif'],
    },
    quantity: {
      type: Number,
      required: [true, 'Jumlah item wajib diisi'],
      min: [1, 'Jumlah item minimal 1'],
      validate: {
        validator: Number.isInteger,
        message: 'Jumlah item harus bilangan bulat',
      },
    },
    purchase_link: {
      type: String,
      trim: true,
      match: [/^https?:\/\/.+/, 'Link pembelian harus berupa URL yang valid'],
      default: null,
    },
    /**
     * Jika item ini merupakan pengganti inventaris lama,
     * isi dengan ID inventaris yang digantikan.
     */
    replacement_inventory_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Inventory',
      default: null,
    },
    approval_status: {
      type: String,
      required: [true, 'Status persetujuan wajib diisi'],
      enum: {
        values: ['pending', 'approved', 'rejected'],
        message: 'Status persetujuan tidak valid. Pilih: pending, approved, rejected',
      },
      default: 'pending',
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
procurementDraftItemSchema.index({ draft_id: 1 });
procurementDraftItemSchema.index({ replacement_inventory_id: 1 });
procurementDraftItemSchema.index({ approval_status: 1 });
procurementDraftItemSchema.index({ draft_id: 1, item_type: 1 });

// --- Virtual Fields ---

/**
 * Menghitung total harga (harga × jumlah).
 */
procurementDraftItemSchema.virtual('total_price').get(function () {
  return this.price * this.quantity;
});

/**
 * Mengembalikan true jika item disetujui.
 */
procurementDraftItemSchema.virtual('is_approved').get(function () {
  return this.approval_status === 'approved';
});

// --- Virtual Relationships ---

/**
 * Mendapatkan semua penerimaan barang terkait item ini.
 */
procurementDraftItemSchema.virtual('receipts', {
  ref: 'ProcurementReceipt',
  localField: '_id',
  foreignField: 'draft_item_id',
});

// --- Instance Methods ---

/**
 * Menyetujui item pengadaan.
 * @returns {Promise<ProcurementDraftItem>}
 */
procurementDraftItemSchema.methods.approve = function () {
  this.approval_status = 'approved';
  return this.save();
};

/**
 * Menolak item pengadaan.
 * @returns {Promise<ProcurementDraftItem>}
 */
procurementDraftItemSchema.methods.reject = function () {
  this.approval_status = 'rejected';
  return this.save();
};

// --- Static Methods ---

/**
 * Mendapatkan semua item berdasarkan ID draft.
 * @param {ObjectId} draftId
 * @returns {Promise<ProcurementDraftItem[]>}
 */
procurementDraftItemSchema.statics.findByDraftId = function (draftId) {
  return this.find({ draft_id: draftId });
};

/**
 * Mendapatkan total nilai pengadaan untuk suatu draft.
 * @param {ObjectId} draftId
 * @returns {Promise<number>}
 */
procurementDraftItemSchema.statics.getTotalValueByDraftId = async function (draftId) {
  const result = await this.aggregate([
    { $match: { draft_id: new mongoose.Types.ObjectId(draftId) } },
    {
      $group: {
        _id: null,
        total: { $sum: { $multiply: ['$price', '$quantity'] } },
      },
    },
  ]);
  return result.length > 0 ? result[0].total : 0;
};

const ProcurementDraftItem = mongoose.model(
  'ProcurementDraftItem',
  procurementDraftItemSchema,
  'procurement_draft_items'
);

module.exports = ProcurementDraftItem;
