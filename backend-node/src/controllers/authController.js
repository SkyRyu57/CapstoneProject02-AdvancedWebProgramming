const jwt = require('jsonwebtoken');
const User = require('../models/User');

const jwtSecret = () => process.env.JWT_SECRET || 'change-this-secret-in-production';

exports.login = async (req, res, next) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(422).json({ message: 'Email dan password wajib diisi.' });
    }

    const user = await User.findByEmail(email);
    const validPassword = user && await User.passwordMatches(password, user.password);

    if (!validPassword) {
      return res.status(401).json({ message: 'Email atau password tidak sesuai.' });
    }

    const token = jwt.sign(
      { userId: user._id, role: user.role, email: user.email },
      jwtSecret(),
      { expiresIn: process.env.JWT_EXPIRES_IN || '8h' },
    );

    return res.json({
      message: 'Login berhasil.',
      token,
      user: User.toPublic(user),
    });
  } catch (error) {
    return next(error);
  }
};

exports.me = async (req, res) => {
  res.json({ user: User.toPublic(req.user) });
};

exports.forgotAccount = async (req, res, next) => {
  try {
    const { email } = req.body;
    const user = email ? await User.findByEmail(email) : null;

    return res.json({
      message: 'Jika akun terdaftar, hubungi administrator laboratorium untuk reset password atau aktivasi ulang.',
      account_hint: user ? User.toAccountHint(user) : null,
      admin_contact: process.env.ADMIN_CONTACT || 'admin.lab@kampus.ac.id',
    });
  } catch (error) {
    return next(error);
  }
};
