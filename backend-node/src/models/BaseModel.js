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

  static async nextId() {
    const latest = await this.findMany({}, { sort: { _id: -1 }, limit: 1 });

    return Number(latest[0]?._id || 0) + 1;
  }

  static async create(data) {
    const now = new Date();
    const document = {
      _id: await this.nextId(),
      ...data,
      created_at: now,
      updated_at: now,
    };

    await this.collection().insertOne(document);

    return document;
  }

  static async updateById(id, data) {
    const numericId = Number(id);
    const update = {
      ...data,
      updated_at: new Date(),
    };

    await this.collection().updateOne({ _id: numericId }, { $set: update });

    return this.findOne({ _id: numericId });
  }

  static async deleteById(id) {
    const numericId = Number(id);
    const document = await this.findOne({ _id: numericId });

    if (!document) {
      return null;
    }

    await this.collection().deleteOne({ _id: numericId });

    return document;
  }
}

module.exports = BaseModel;
