const BaseModel = require('./BaseModel');

class ProcurementDraft extends BaseModel {
  static get collectionName() {
    return 'procurement_drafts';
  }

  static listByKepalaLab(kepalaLabId, limit = 5) {
    return this.findMany(
      { kepala_lab_id: kepalaLabId },
      { sort: { created_at: -1 }, limit },
    );
  }

  static listForReview(limit = 100) {
    return this.findMany(
      { status: { $in: ['submitted', 'finalized'] } },
      { sort: { created_at: -1 }, limit },
    );
  }

  static listByKepalaLabAll(kepalaLabId, limit = 100) {
    return this.findMany(
      { kepala_lab_id: kepalaLabId },
      { sort: { created_at: -1 }, limit },
    );
  }

  static findById(id) {
    return this.findOne({ _id: Number(id) });
  }

  static listFinalized(limit = 100) {
    return this.findMany(
      { status: 'finalized' },
      { sort: { finalized_at: -1, created_at: -1 }, limit },
    );
  }

  static createDraft(data) {
    return this.create({
      kepala_lab_id: Number(data.kepala_lab_id),
      fiscal_year: Number(data.fiscal_year),
      status: 'draft',
      notes: data.notes || '',
      reviewer_id: null,
      finalized_at: null,
    });
  }

  static async submitDraft(id) {
    const draft = await this.findById(id);

    if (!draft || draft.status !== 'draft') return null;

    return this.updateById(id, {
      status: 'submitted',
      submitted_at: new Date(),
    });
  }

  static async updateDraft(id, data) {
    const draft = await this.findById(id);

    if (!draft || draft.status === 'finalized') return null;

    return this.updateById(id, {
      fiscal_year: Number(data.fiscal_year),
      notes: data.notes || '',
    });
  }

  static async deleteDraft(id) {
    const draft = await this.findById(id);

    if (!draft || draft.status === 'finalized') return null;

    return this.deleteById(id);
  }

  static async finalize(id, reviewerId) {
    const draft = await this.findById(id);

    if (!draft) {
      return null;
    }

    if (draft.status === 'finalized') {
      return draft;
    }

    return this.updateById(id, {
      status: 'finalized',
      reviewer_id: reviewerId,
      finalized_at: new Date(),
    });
  }

  static async finalizedProcurementValue() {
    const rows = await this.aggregate([
      { $match: { status: 'finalized' } },
      {
        $lookup: {
          from: 'procurement_draft_items',
          localField: '_id',
          foreignField: 'draft_id',
          as: 'items',
        },
      },
      { $unwind: { path: '$items', preserveNullAndEmptyArrays: true } },
      {
        $group: {
          _id: null,
          value: {
            $sum: {
              $multiply: [
                { $ifNull: ['$items.price', 0] },
                { $ifNull: ['$items.quantity', 0] },
              ],
            },
          },
        },
      },
    ]);

    return rows[0]?.value || 0;
  }
}

module.exports = ProcurementDraft;
