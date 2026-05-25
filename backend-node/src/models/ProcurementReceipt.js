const BaseModel = require('./BaseModel');

class ProcurementReceipt extends BaseModel {
  static get collectionName() {
    return 'procurement_receipts';
  }

  static listRecent(limit = 6) {
    return this.findMany({}, { sort: { received_date: -1 }, limit });
  }
}

module.exports = ProcurementReceipt;
