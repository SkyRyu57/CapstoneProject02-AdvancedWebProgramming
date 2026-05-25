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

  static listForReview(limit = 5) {
    return this.findMany(
      { status: { $in: ['submitted', 'finalized'] } },
      { sort: { created_at: -1 }, limit },
    );
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
