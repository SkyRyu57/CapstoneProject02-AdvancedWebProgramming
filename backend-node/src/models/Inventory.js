const BaseModel = require('./BaseModel');

class Inventory extends BaseModel {
  static get collectionName() {
    return 'inventories';
  }

  static countInMaintenance() {
    return this.count({ status: 'maintenance' });
  }
}

module.exports = Inventory;
