const roleLabels = {
  admin: 'Administrator',
  kepala_lab: 'Kepala Laboratorium',
  kaprodi: 'Ketua Program Studi',
  staf_admin: 'Staf Administrasi',
  staf_lab: 'Staf Laboratorium',
};

const roleDescriptions = {
  admin: 'Kelola pengguna, ruangan, dan kesiapan data master laboratorium.',
  kepala_lab: 'Susun draf pengadaan tahunan dan pantau status review pengajuan.',
  kaprodi: 'Review draf pengadaan, setujui item prioritas, lalu finalisasi pengajuan.',
  staf_admin: 'Catat penerimaan barang dan lengkapi label serta QR/barcode inventaris.',
  staf_lab: 'Pantau stok BHP, maintenance inventaris, dan pemakaian bahan saat perawatan.',
};

const roleActions = {
  admin: ['Tambah akun pengguna', 'Kelola ruangan', 'Audit role dan akses'],
  kepala_lab: ['Buat draf pengadaan', 'Edit draf belum locked', 'Lihat riwayat pengajuan'],
  kaprodi: ['Review item pengadaan', 'Tolak/setujui item', 'Finalisasi draf'],
  staf_admin: ['Input tanggal penerimaan', 'Lengkapi label aset', 'Unggah QR/barcode'],
  staf_lab: ['Update stok BHP', 'Catat maintenance', 'Kurangi BHP terpakai'],
};

module.exports = {
  roleActions,
  roleDescriptions,
  roleLabels,
};
