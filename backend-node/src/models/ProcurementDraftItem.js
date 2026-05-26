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

  static listApproved(limit = 100) {
    return this.findMany(
      { approval_status: 'approved' },
      { sort: { _id: -1 }, limit },
    );
  }

  static findById(id) {
    return this.findOne({ _id: Number(id) });
  }

  static review(id, approvalStatus) {
    return this.updateById(id, { approval_status: approvalStatus });
  }

  static createItem(data) {
    return this.create({
      draft_id: Number(data.draft_id),
      item_type: data.item_type || 'inventory',
      name: data.name,
      price: Number(data.price) || 0,
      quantity: Number(data.quantity) || 1,
      purchase_link: data.purchase_link || '',
      replacement_inventory_id: data.replacement_inventory_id ? Number(data.replacement_inventory_id) : null,
      approval_status: 'pending',
    });
  }

  static async updateItem(id, data) {
    return this.updateById(id, {
      item_type: data.item_type,
      name: data.name,
      price: Number(data.price) || 0,
      quantity: Number(data.quantity) || 1,
      purchase_link: data.purchase_link || '',
      replacement_inventory_id: data.replacement_inventory_id ? Number(data.replacement_inventory_id) : null,
    });
  }

  static destroyItem(id) {
    return this.deleteById(id);
  }
}

module.exports = ProcurementDraftItem;
