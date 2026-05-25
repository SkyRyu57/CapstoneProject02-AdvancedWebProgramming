const Consumable = require('./Consumable');
const Inventory = require('./Inventory');
const InventoryMaintenanceLog = require('./InventoryMaintenanceLog');
const ProcurementDraft = require('./ProcurementDraft');
const ProcurementDraftItem = require('./ProcurementDraftItem');
const ProcurementReceipt = require('./ProcurementReceipt');
const Room = require('./Room');
const User = require('./User');
const { roleActions, roleDescriptions, roleLabels } = require('../config/roles');

class Dashboard {
  static rupiah(value) {
    return Number(value || 0).toLocaleString('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    });
  }

  static async forUser(user) {
    return {
      role: user.role,
      role_label: roleLabels[user.role] || user.role,
      description: roleDescriptions[user.role] || 'Dashboard sistem inventaris laboratorium.',
      stats: await this.baseStats(),
      actions: roleActions[user.role] || [],
      sections: await this.sectionsFor(user),
    };
  }

  static async baseStats() {
    const [
      users,
      rooms,
      inventories,
      consumables,
      drafts,
      finalizedDrafts,
      submittedDrafts,
      lowStock,
      activeMaintenance,
      approvedValue,
    ] = await Promise.all([
      User.count(),
      Room.count(),
      Inventory.count(),
      Consumable.count(),
      ProcurementDraft.count(),
      ProcurementDraft.count({ status: 'finalized' }),
      ProcurementDraft.count({ status: 'submitted' }),
      Consumable.countLowStock(),
      Inventory.countInMaintenance(),
      ProcurementDraft.finalizedProcurementValue(),
    ]);

    return [
      { label: 'Pengguna', value: users, tone: 'neutral' },
      { label: 'Ruangan Lab', value: rooms, tone: 'info' },
      { label: 'Inventaris', value: inventories, tone: 'success' },
      { label: 'Jenis BHP', value: consumables, tone: 'warning' },
      { label: 'Draf Pengadaan', value: drafts, tone: 'info' },
      { label: 'Draf Final', value: finalizedDrafts, tone: 'success' },
      { label: 'Menunggu Review', value: submittedDrafts, tone: 'warning' },
      { label: 'Stok Menipis', value: lowStock, tone: 'danger' },
      { label: 'Maintenance Aktif', value: activeMaintenance, tone: 'danger' },
      { label: 'Nilai Final', value: this.rupiah(approvedValue), tone: 'success' },
    ];
  }

  static async sectionsFor(user) {
    const sectionBuilders = {
      admin: () => this.adminSections(),
      kepala_lab: () => this.kepalaLabSections(user._id),
      kaprodi: () => this.kaprodiSections(),
      staf_admin: () => this.stafAdminSections(),
      staf_lab: () => this.stafLabSections(),
    };

    return sectionBuilders[user.role]?.() || [];
  }

  static async adminSections() {
    const [users, rooms] = await Promise.all([
      User.listPublic(),
      Room.listRecent(),
    ]);

    return [
      {
        title: 'Pengguna Sistem',
        columns: ['Nama', 'Email', 'Role'],
        rows: users.map((user) => [user.name, user.email, roleLabels[user.role] || user.role]),
      },
      {
        title: 'Data Ruangan',
        columns: ['Ruangan', 'Deskripsi'],
        rows: rooms.map((room) => [room.name, room.description]),
      },
    ];
  }

  static async kepalaLabSections(userId) {
    const drafts = await ProcurementDraft.listByKepalaLab(userId);
    const items = await ProcurementDraftItem.listByDraftIds(drafts.map((draft) => draft._id));

    return [
      {
        title: 'Draf Pengadaan Saya',
        columns: ['Tahun', 'Status', 'Jumlah Item', 'Catatan'],
        rows: drafts.map((draft) => [
          draft.fiscal_year,
          draft.status,
          items.filter((item) => item.draft_id === draft._id).length,
          draft.notes,
        ]),
      },
    ];
  }

  static async kaprodiSections() {
    const drafts = await ProcurementDraft.listForReview();
    const items = await ProcurementDraftItem.listByDraftIds(drafts.map((draft) => draft._id));

    return [
      {
        title: 'Review Draf Pengadaan',
        columns: ['Tahun', 'Status', 'Pending', 'Disetujui', 'Ditolak'],
        rows: drafts.map((draft) => {
          const draftItems = items.filter((item) => item.draft_id === draft._id);

          return [
            draft.fiscal_year,
            draft.status,
            draftItems.filter((item) => item.approval_status === 'pending').length,
            draftItems.filter((item) => item.approval_status === 'approved').length,
            draftItems.filter((item) => item.approval_status === 'rejected').length,
          ];
        }),
      },
    ];
  }

  static async stafAdminSections() {
    const [items, receipts] = await Promise.all([
      ProcurementDraftItem.listApproved(),
      ProcurementReceipt.listRecent(),
    ]);

    return [
      {
        title: 'Barang Disetujui',
        columns: ['Barang', 'Tipe', 'Jumlah', 'Harga'],
        rows: items.map((item) => [item.name, item.item_type, item.quantity, this.rupiah(item.price)]),
      },
      {
        title: 'Riwayat Penerimaan',
        columns: ['Item ID', 'Tanggal', 'Jumlah', 'Catatan'],
        rows: receipts.map((receipt) => [
          receipt.draft_item_id,
          receipt.received_date ? receipt.received_date.toISOString().slice(0, 10) : '-',
          receipt.quantity,
          receipt.notes,
        ]),
      },
    ];
  }

  static async stafLabSections() {
    const [lowStock, maintenance] = await Promise.all([
      Consumable.listLowStock(),
      InventoryMaintenanceLog.listRecent(),
    ]);

    return [
      {
        title: 'BHP Perlu Restock',
        columns: ['Nama', 'Stok', 'Minimum', 'Satuan'],
        rows: lowStock.map((item) => [item.name, item.stock, item.min_stock, item.unit]),
      },
      {
        title: 'Log Maintenance Terakhir',
        columns: ['Inventaris ID', 'Tanggal', 'Kondisi Akhir', 'Catatan'],
        rows: maintenance.map((log) => [
          log.inventory_item_id,
          log.maintenance_date ? log.maintenance_date.toISOString().slice(0, 10) : '-',
          log.condition_after,
          log.description,
        ]),
      },
    ];
  }
}

module.exports = Dashboard;
