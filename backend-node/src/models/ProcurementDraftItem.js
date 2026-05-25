const BaseModel = require('./BaseModel');

class ProcurementDraftItem extends BaseModel {
  static get collectionName() {
    return 'procurement_draft_items';
  }

  static listByDraftIds(draftIds) {
    if (!draftIds.length) {
      return [];
    }

    return this.findMany({ draft_id: { $in: draftIds } });
  }

  static listApproved(limit = 6) {
    return this.findMany(
      { approval_status: 'approved' },
      { sort: { _id: -1 }, limit },
    );
  }
}

module.exports = ProcurementDraftItem;
