const jwt = require('jsonwebtoken');
const User = require('../models/User');

const jwtSecret = () => process.env.JWT_SECRET || 'change-this-secret-in-production';

module.exports = async function authenticate(req, res, next) {
  try {
    const header = req.headers.authorization || '';
    const token = header.startsWith('Bearer ') ? header.slice(7) : null;

    if (!token) {
      return res.status(401).json({ message: 'Token tidak ditemukan.' });
    }

    const payload = jwt.verify(token, jwtSecret());
    const user = await User.findPublicById(payload.userId);

    if (!user) {
      return res.status(401).json({ message: 'Akun tidak ditemukan.' });
    }

    req.user = user;
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Sesi tidak valid atau sudah kedaluwarsa.' });
  }
};
