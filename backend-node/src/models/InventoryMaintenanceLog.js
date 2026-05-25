const BaseModel = require('./BaseModel');

class InventoryMaintenanceLog extends BaseModel {
  static get collectionName() {
    return 'inventory_maintenance_logs';
  }

  static listRecent(limit = 6) {
    return this.findMany({}, { sort: { maintenance_date: -1 }, limit });
  }
}

module.exports = InventoryMaintenanceLog;
