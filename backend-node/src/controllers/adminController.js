const { roleLabels } = require('../config/roles');
const Room = require('../models/Room');
const User = require('../models/User');

function validationError(res, message) {
  return res.status(422).json({ message });
}

exports.users = async (req, res, next) => {
  try {
    const users = await User.listPublic();

    res.json({
      roles: roleLabels,
      users: users.map((user) => User.toPublic(user)),
    });
  } catch (error) {
    next(error);
  }
};

exports.storeUser = async (req, res, next) => {
  try {
    const { name, email, password, role } = req.body;

    if (!name || !email || !password || !role) {
      return validationError(res, 'Nama, email, password, dan role wajib diisi.');
    }

    if (!roleLabels[role]) {
      return validationError(res, 'Role tidak valid.');
    }

    const existingUser = await User.findByEmail(email);

    if (existingUser) {
      return validationError(res, 'Email sudah digunakan.');
    }

    res.status(201).json({
      message: 'Pengguna berhasil dibuat.',
      user: await User.createAccount({ name, email, password, role }),
    });
  } catch (error) {
    next(error);
  }
};

exports.updateUser = async (req, res, next) => {
  try {
    const { name, email, password, role } = req.body;

    if (!name || !email || !role) {
      return validationError(res, 'Nama, email, dan role wajib diisi.');
    }

    if (!roleLabels[role]) {
      return validationError(res, 'Role tidak valid.');
    }

    const existingUser = await User.findByEmail(email);

    if (existingUser && existingUser._id !== Number(req.params.id)) {
      return validationError(res, 'Email sudah digunakan oleh akun lain.');
    }

    const user = await User.updateAccount(req.params.id, { name, email, password, role });

    if (!user) {
      return res.status(404).json({ message: 'Pengguna tidak ditemukan.' });
    }

    res.json({
      message: 'Pengguna berhasil diperbarui.',
      user,
    });
  } catch (error) {
    next(error);
  }
};

exports.destroyUser = async (req, res, next) => {
  try {
    const userId = Number(req.params.id);

    if (req.user._id === userId) {
      return validationError(res, 'Akun yang sedang login tidak boleh dihapus.');
    }

    const user = await User.deleteAccount(userId);

    if (!user) {
      return res.status(404).json({ message: 'Pengguna tidak ditemukan.' });
    }

    return res.json({ message: 'Pengguna berhasil dihapus.' });
  } catch (error) {
    return next(error);
  }
};

exports.rooms = async (req, res, next) => {
  try {
    res.json({ rooms: await Room.listRecent() });
  } catch (error) {
    next(error);
  }
};

exports.storeRoom = async (req, res, next) => {
  try {
    const { name, description } = req.body;

    if (!name) {
      return validationError(res, 'Nama ruangan wajib diisi.');
    }

    res.status(201).json({
      message: 'Ruangan berhasil dibuat.',
      room: await Room.createRoom({ name, description }),
    });
  } catch (error) {
    next(error);
  }
};

exports.updateRoom = async (req, res, next) => {
  try {
    const { name, description } = req.body;

    if (!name) {
      return validationError(res, 'Nama ruangan wajib diisi.');
    }

    const room = await Room.updateRoom(req.params.id, { name, description });

    if (!room) {
      return res.status(404).json({ message: 'Ruangan tidak ditemukan.' });
    }

    return res.json({
      message: 'Ruangan berhasil diperbarui.',
      room,
    });
  } catch (error) {
    next(error);
  }
};

exports.destroyRoom = async (req, res, next) => {
  try {
    const room = await Room.deleteRoom(req.params.id);

    if (!room) {
      return res.status(404).json({ message: 'Ruangan tidak ditemukan.' });
    }

    return res.json({ message: 'Ruangan berhasil dihapus.' });
  } catch (error) {
    return next(error);
  }
};
