module.exports = function authorize(...allowedRoles) {
  return function checkRole(req, res, next) {
    if (!req.user || !allowedRoles.includes(req.user.role)) {
      return res.status(403).json({ message: 'Anda tidak memiliki akses ke fitur ini.' });
    }

    return next();
  };
};
