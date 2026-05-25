const mongoose = require('mongoose');

/**
 * ProcurementDraft Schema
 * Merepresentasikan rancangan/draft pengadaan barang laboratorium.
 * Alur status:
 *   draft -> submitted -> approved/rejected -> finalized
 *
 * Dibuat oleh kepala_lab, direview oleh kaprodi.
 */
const procurementDraftSchema = new mongoose.Schema(
  {
    kepala_lab_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: [true, 'ID kepala lab wajib diisi'],
    },
    fiscal_year: {
      type: Number,
      required: [true, 'Tahun anggaran wajib diisi'],
      min: [2000, 'Tahun anggaran tidak valid'],
      max: [2100, 'Tahun anggaran tidak valid'],
    },
    status: {
      type: String,
      required: [true, 'Status wajib diisi'],
      enum: {
        values: ['draft', 'submitted', 'approved', 'rejected', 'finalized'],
        message: 'Status tidak valid. Pilih: draft, submitted, approved, rejected, finalized',
      },
      default: 'draft',
    },
    notes: {
      type: String,
      trim: true,
      maxlength: [1000, 'Catatan tidak boleh lebih dari 1000 karakter'],
      default: null,
    },
    reviewer_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      default: null,
    },
    finalized_at: {
      type: Date,
      default: null,
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
procurementDraftSchema.index({ kepala_lab_id: 1 });
procurementDraftSchema.index({ reviewer_id: 1 });
procurementDraftSchema.index({ status: 1 });
procurementDraftSchema.index({ fiscal_year: 1 });
procurementDraftSchema.index({ fiscal_year: 1, status: 1 });

// --- Virtual Relationships ---

/**
 * Mendapatkan semua item dalam draft pengadaan ini.
 */
procurementDraftSchema.virtual('items', {
  ref: 'ProcurementDraftItem',
  localField: '_id',
  foreignField: 'draft_id',
});

// --- Virtual Fields ---

/**
 * Mengembalikan true jika draft sudah difinalisasi.
 */
procurementDraftSchema.virtual('is_finalized').get(function () {
  return this.finalized_at !== null;
});

// --- Instance Methods ---

/**
 * Mengubah status draft menjadi submitted.
 * @returns {Promise<ProcurementDraft>}
 */
procurementDraftSchema.methods.submit = function () {
  if (this.status !== 'draft') {
    throw new Error('Hanya draft yang dapat disubmit');
  }
  this.status = 'submitted';
  return this.save();
};

/**
 * Menyetujui draft pengadaan.
 * @param {ObjectId} reviewerId - ID user yang menyetujui
 * @returns {Promise<ProcurementDraft>}
 */
procurementDraftSchema.methods.approve = function (reviewerId) {
  if (this.status !== 'submitted') {
    throw new Error('Hanya draft yang sudah disubmit yang dapat disetujui');
  }
  this.status = 'approved';
  this.reviewer_id = reviewerId;
  return this.save();
};

/**
 * Menolak draft pengadaan.
 * @param {ObjectId} reviewerId - ID user yang menolak
 * @param {string} reason - Alasan penolakan
 * @returns {Promise<ProcurementDraft>}
 */
procurementDraftSchema.methods.reject = function (reviewerId, reason = null) {
  if (this.status !== 'submitted') {
    throw new Error('Hanya draft yang sudah disubmit yang dapat ditolak');
  }
  this.status = 'rejected';
  this.reviewer_id = reviewerId;
  if (reason) this.notes = reason;
  return this.save();
};

/**
 * Memfinalisasi draft pengadaan.
 * @returns {Promise<ProcurementDraft>}
 */
procurementDraftSchema.methods.finalize = function () {
  if (this.status !== 'approved') {
    throw new Error('Hanya draft yang sudah disetujui yang dapat difinalisasi');
  }
  this.status = 'finalized';
  this.finalized_at = new Date();
  return this.save();
};

const ProcurementDraft = mongoose.model(
  'ProcurementDraft',
  procurementDraftSchema,
  'procurement_drafts'
);

module.exports = ProcurementDraft;
