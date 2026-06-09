const BaseModel = require('./BaseModel');

class ProcurementReceipt extends BaseModel {
  static get collectionName() {
    return 'procurement_receipts';
  }

  static listRecent(limit = 100) {
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

  /**
   * Return { [draft_item_id]: totalQuantityReceived } map for a list of item IDs.
   */
  static async sumByDraftItemIds(itemIds) {
    if (!itemIds.length) return {};

    const rows = await this.aggregate([
      { $match: { draft_item_id: { $in: itemIds.map(Number) } } },
      {
        $group: {
          _id: '$draft_item_id',
          total: { $sum: '$quantity' },
        },
      },
    ]);

    return rows.reduce((acc, row) => {
      acc[row._id] = row.total;
      return acc;
    }, {});
  }
}

module.exports = ProcurementReceipt;
