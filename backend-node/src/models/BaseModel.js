const mongoose = require('mongoose');

class BaseModel {
  static get collectionName() {
    throw new Error('collectionName must be defined by child model.');
  }

  static collection() {
    return mongoose.connection.db.collection(this.collectionName);
  }

  static count(query = {}) {
    return this.collection().countDocuments(query);
  }

  static findOne(query = {}, options = {}) {
    return this.collection().findOne(query, options);
  }

  static findMany(query = {}, options = {}) {
    return this.collection().find(query, options).toArray();
  }

  static aggregate(pipeline = []) {
    return this.collection().aggregate(pipeline).toArray();
  }
}

module.exports = BaseModel;
