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
}

module.exports = Inventory;
