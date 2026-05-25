/**
 * @deprecated Gunakan User.toPublic(user) dari models/User.js.
 * File ini disimpan untuk kompatibilitas mundur sementara.
 */
const { roleLabels } = require('../config/roles');
const User = require('../models/User');

/** @deprecated Gunakan User.toPublic(user) */
const publicUser = (user) => User.toPublic(user);

module.exports = { publicUser, roleLabels };
