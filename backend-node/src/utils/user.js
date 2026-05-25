const { roleLabels } = require('../config/roles');

function publicUser(user) {
  return {
    id: user._id,
    name: user.name,
    email: user.email,
    role: user.role,
    role_label: roleLabels[user.role] || user.role,
  };
}

module.exports = { publicUser, roleLabels };
