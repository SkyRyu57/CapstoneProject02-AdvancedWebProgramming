const BaseModel = require('./BaseModel');

class ProcurementReceipt extends BaseModel {
  static get collectionName() {
    return 'procurement_receipts';
  }

  static listRecent(limit = 6) {
    return this.findMany({}, { sort: { received_date: -1 }, limit });
  }

  static createReceipt(data) {
    return this.create({
      draft_item_id: Number(data.draft_item_id),
      received_date: new Date(data.received_date),
      quantity: Number(data.quantity),
      staf_admin_id: Number(data.staf_admin_id),
      notes: data.notes || '',
    });
  }
}

module.exports = ProcurementReceipt;
