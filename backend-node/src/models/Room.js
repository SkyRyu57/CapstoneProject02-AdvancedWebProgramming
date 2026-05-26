const BaseModel = require('./BaseModel');

class Room extends BaseModel {
  static get collectionName() {
    return 'rooms';
  }

  static listRecent(limit = 100) {
    return this.findMany({}, { sort: { _id: 1 }, limit });
  }

  static createRoom(data) {
    return this.create({
      name: data.name,
      description: data.description || '',
    });
  }

  static updateRoom(id, data) {
    return this.updateById(id, {
      name: data.name,
      description: data.description || '',
    });
  }

  static deleteRoom(id) {
    return this.deleteById(id);
  }
}

module.exports = Room;
