const BaseModel = require('./BaseModel');

class Inventory extends BaseModel {
  static get collectionName() {
    return 'inventories';
  }

  static countInMaintenance() {
    return this.count({ status: 'maintenance' });
  }

  static listManageable(limit = 100) {
    return this.findMany({}, { sort: { _id: 1 }, limit });
  }

  static updateAssetData(id, data) {
    const payload = {
      label_code: data.label_code || null,
      qr_code: data.qr_code || null,
      room_id: Number(data.room_id) || null,
      condition: data.condition || 'baik',
      status: data.status || 'active',
    };

    return this.updateById(id, payload);
  }

  static deleteInventory(id) {
    return this.deleteById(id);
  }

  static listForStafLab(limit = 200) {
    return this.findMany(
      { status: { $ne: 'retired' } },
      { sort: { name: 1 }, limit },
    );
  }

  static findById(id) {
    return this.findOne({ _id: Number(id) });
  }

  static updateCondition(id, condition, status) {
    const payload = { condition };
    if (status) payload.status = status;
    return this.updateById(id, payload);
  }
}

module.exports = Inventory;
