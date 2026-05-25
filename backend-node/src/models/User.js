const bcrypt = require('bcrypt');
const BaseModel = require('./BaseModel');
const { roleLabels } = require('../config/roles');

class User extends BaseModel {
  static get collectionName() {
    return 'users';
  }

  static normalizeEmail(email) {
    return String(email || '').trim().toLowerCase();
  }

  static findByEmail(email) {
    return this.findOne({ email: this.normalizeEmail(email) });
  }

  static findPublicById(id) {
    return this.findOne({ _id: id }, { projection: { password: 0 } });
  }

  static listPublic(limit = 5) {
    return this.findMany({}, {
      projection: { password: 0 },
      sort: { _id: 1 },
      limit,
    });
  }

  static async passwordMatches(inputPassword, storedPassword) {
    if (!storedPassword) {
      return false;
    }

    if (storedPassword.startsWith('$2a$') || storedPassword.startsWith('$2b$') || storedPassword.startsWith('$2y$')) {
      return bcrypt.compare(inputPassword, storedPassword);
    }

    return inputPassword === storedPassword;
  }

  static toPublic(user) {
    return {
      id: user._id,
      name: user.name,
      email: user.email,
      role: user.role,
      role_label: roleLabels[user.role] || user.role,
    };
  }

  static toAccountHint(user) {
    return {
      name: user.name,
      email: user.email,
      role: user.role,
    };
  }
}

module.exports = User;
