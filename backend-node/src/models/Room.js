const BaseModel = require('./BaseModel');

class Room extends BaseModel {
  static get collectionName() {
    return 'rooms';
  }

  static listRecent(limit = 5) {
    return this.findMany({}, { sort: { _id: 1 }, limit });
  }
}

module.exports = Room;
