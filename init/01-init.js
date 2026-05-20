db = db.getSiblingDB('lab_asset');

db.users.drop();
db.rooms.drop();
db.user_room_shifts.drop();
db.procurement_drafts.drop();
db.procurement_draft_items.drop();
db.procurement_receipts.drop();
db.inventories.drop();
db.consumables.drop();
db.consumable_stock_adjustments.drop();
db.inventory_maintenance_logs.drop();
db.maintenance_consumable_usages.drop();

// USERS
db.users.insertMany([
  {
    _id: 1,
    name: "Nadia Putri",
    email: "nadia@kampus.ac.id",
    password: "password_demo",
    role: "admin",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    name: "Dr. Reza Mahendra",
    email: "reza@kampus.ac.id",
    password: "password_demo",
    role: "kepala_lab",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 3,
    name: "Prof. Arman Wirawan",
    email: "arman@kampus.ac.id",
    password: "password_demo",
    role: "kaprodi",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 4,
    name: "Dinda Prameswari",
    email: "dinda@kampus.ac.id",
    password: "password_demo",
    role: "staf_admin",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 5,
    name: "Bagas Satria",
    email: "bagas@kampus.ac.id",
    password: "password_demo",
    role: "staf_lab",
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// ROOMS
db.rooms.insertMany([
  {
    _id: 1,
    name: "LAB-301 Laboratorium Jaringan",
    description: "Ruang praktikum jaringan komputer dan perangkat switching-routing.",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    name: "LAB-302 Laboratorium Pemrograman",
    description: "Ruang praktikum pemrograman dan basis data.",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 3,
    name: "LAB-303 Laboratorium Multimedia",
    description: "Ruang praktikum multimedia dan produksi konten.",
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// USER ROOM SHIFTS
db.user_room_shifts.insertMany([
  {
    _id: 1,
    user_id: 5,
    room_id: 1,
    shift_date: new Date("2026-05-18"),
    start_time: "08:00:00",
    end_time: "12:00:00",
    finalized_at: null,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    user_id: 5,
    room_id: 2,
    shift_date: new Date("2026-05-18"),
    start_time: "13:00:00",
    end_time: "17:00:00",
    finalized_at: null,
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// PROCUREMENT DRAFTS
db.procurement_drafts.insertMany([
  {
    _id: 1,
    kepala_lab_id: 2,
    fiscal_year: 2026,
    status: "submitted",
    notes: "Pengadaan tahunan untuk refresh perangkat jaringan.",
    reviewer_id: 3,
    finalized_at: null,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    kepala_lab_id: 2,
    fiscal_year: 2026,
    status: "finalized",
    notes: "Pengadaan lab pemrograman sudah difinalisasi.",
    reviewer_id: 3,
    finalized_at: new Date("2026-04-01T10:00:00"),
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// PROCUREMENT DRAFT ITEMS
db.procurement_draft_items.insertMany([
  {
    _id: 1,
    draft_id: 1,
    item_type: "inventory",
    name: "Router MikroTik CCR2004",
    price: 7800000.00,
    quantity: 4,
    purchase_link: "https://example.com/router",
    replacement_inventory_id: null,
    approval_status: "pending",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    draft_id: 1,
    item_type: "consumable",
    name: "Kabel UTP Cat6",
    price: 4500.00,
    quantity: 500,
    purchase_link: "https://example.com/kabel",
    replacement_inventory_id: null,
    approval_status: "approved",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 3,
    draft_id: 1,
    item_type: "consumable",
    name: "RJ45 Connector",
    price: 800.00,
    quantity: 1000,
    purchase_link: "https://example.com/rj45",
    replacement_inventory_id: null,
    approval_status: "approved",
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 4,
    draft_id: 2,
    item_type: "inventory",
    name: "PC Praktikum Core i7 Gen 14",
    price: 14500000.00,
    quantity: 12,
    purchase_link: "https://example.com/pc",
    replacement_inventory_id: 2,
    approval_status: "approved",
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// PROCUREMENT RECEIPTS
db.procurement_receipts.insertMany([
  {
    _id: 1,
    draft_item_id: 2,
    received_date: new Date("2026-04-18"),
    quantity: 200,
    staf_admin_id: 4,
    notes: "Pengiriman tahap pertama.",
    created_at: new Date()
  },
  {
    _id: 2,
    draft_item_id: 3,
    received_date: new Date("2026-04-20"),
    quantity: 1000,
    staf_admin_id: 4,
    notes: "Barang diterima lengkap.",
    created_at: new Date()
  },
  {
    _id: 3,
    draft_item_id: 4,
    received_date: new Date("2026-04-05"),
    quantity: 6,
    staf_admin_id: 4,
    notes: "PC diterima sebagian.",
    created_at: new Date()
  }
]);

// INVENTORIES
db.inventories.insertMany([
  {
    _id: 1,
    name: "Router MikroTik RB4011",
    description: "Router praktikum jaringan.",
    price: 5200000.00,
    condition: "baik",
    label_code: "AST/2026/0001",
    qr_code: "qr-router-001.png",
    room_id: 1,
    status: "active",
    procurement_receipt_id: null,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    name: "PC Praktikum Core i7",
    description: "Komputer praktikum pemrograman.",
    price: 11000000.00,
    condition: "perlu maintenance",
    label_code: "AST/2026/0014",
    qr_code: "qr-pc-014.png",
    room_id: 2,
    status: "maintenance",
    procurement_receipt_id: null,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 3,
    name: "Kamera Dokumentasi Sony",
    description: "Kamera dokumentasi kegiatan lab.",
    price: 9500000.00,
    condition: "baik",
    label_code: "AST/2025/0088",
    qr_code: "qr-camera-007.png",
    room_id: 3,
    status: "active",
    procurement_receipt_id: null,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 4,
    name: "PC Praktikum Core i7 Gen 14",
    description: "PC baru hasil pengadaan tahun 2026.",
    price: 14500000.00,
    condition: "baik",
    label_code: "AST/2026/0101",
    qr_code: "qr-pc-new-001.png",
    room_id: 2,
    status: "active",
    procurement_receipt_id: 3,
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// CONSUMABLES
db.consumables.insertMany([
  {
    _id: 1,
    name: "Kabel UTP Cat6",
    description: "Kabel jaringan untuk praktikum dan maintenance.",
    unit: "meter",
    stock: 145,
    min_stock: 60,
    room_id: 1,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 2,
    name: "RJ45 Connector",
    description: "Konektor RJ45 untuk terminasi kabel.",
    unit: "pcs",
    stock: 420,
    min_stock: 150,
    room_id: 1,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 3,
    name: "Thermal Paste",
    description: "Thermal paste untuk maintenance PC.",
    unit: "tube",
    stock: 18,
    min_stock: 12,
    room_id: 2,
    created_at: new Date(),
    updated_at: new Date()
  },
  {
    _id: 4,
    name: "Label Aset",
    description: "Label stiker untuk penomoran aset.",
    unit: "lembar",
    stock: 38,
    min_stock: 50,
    room_id: 2,
    created_at: new Date(),
    updated_at: new Date()
  }
]);

// CONSUMABLE STOCK ADJUSTMENTS
db.consumable_stock_adjustments.insertMany([
  {
    _id: 1,
    consumable_id: 1,
    quantity_change: 200,
    reason: "Penerimaan pengadaan tahap pertama",
    reference_type: "procurement_receipt",
    reference_id: 1,
    created_by: 4,
    created_at: new Date()
  },
  {
    _id: 2,
    consumable_id: 2,
    quantity_change: 1000,
    reason: "Penerimaan pengadaan lengkap",
    reference_type: "procurement_receipt",
    reference_id: 2,
    created_by: 4,
    created_at: new Date()
  },
  {
    _id: 3,
    consumable_id: 4,
    quantity_change: -12,
    reason: "Pemakaian label inventaris baru",
    reference_type: "manual",
    reference_id: null,
    created_by: 4,
    created_at: new Date()
  }
]);

// INVENTORY MAINTENANCE LOGS
db.inventory_maintenance_logs.insertMany([
  {
    _id: 1,
    inventory_item_id: 2,
    staf_lab_id: 5,
    maintenance_date: new Date("2026-04-26"),
    description: "Pembersihan kipas dan penggantian thermal paste.",
    condition_before: "perlu maintenance",
    condition_after: "baik",
    created_at: new Date()
  },
  {
    _id: 2,
    inventory_item_id: 1,
    staf_lab_id: 5,
    maintenance_date: new Date("2026-04-28"),
    description: "Pemeriksaan konfigurasi dan update firmware.",
    condition_before: "baik",
    condition_after: "baik",
    created_at: new Date()
  }
]);

// MAINTENANCE CONSUMABLE USAGES
db.maintenance_consumable_usages.insertMany([
  {
    _id: 1,
    maintenance_log_id: 1,
    consumable_id: 3,
    quantity_used: 1
  }
]);

// INDEXES
db.users.createIndex({ email: 1 }, { unique: true });
db.inventories.createIndex({ label_code: 1 }, { unique: true });

db.user_room_shifts.createIndex({ user_id: 1 });
db.user_room_shifts.createIndex({ room_id: 1 });
db.user_room_shifts.createIndex({ shift_date: 1 });

db.procurement_drafts.createIndex({ kepala_lab_id: 1 });
db.procurement_drafts.createIndex({ reviewer_id: 1 });
db.procurement_drafts.createIndex({ status: 1 });
db.procurement_drafts.createIndex({ fiscal_year: 1 });

db.procurement_draft_items.createIndex({ draft_id: 1 });
db.procurement_draft_items.createIndex({ replacement_inventory_id: 1 });
db.procurement_draft_items.createIndex({ approval_status: 1 });

db.procurement_receipts.createIndex({ draft_item_id: 1 });
db.procurement_receipts.createIndex({ staf_admin_id: 1 });
db.procurement_receipts.createIndex({ received_date: 1 });

db.inventories.createIndex({ room_id: 1 });
db.inventories.createIndex({ status: 1 });
db.inventories.createIndex({ procurement_receipt_id: 1 });

db.consumables.createIndex({ room_id: 1 });
db.consumables.createIndex({ stock: 1 });

db.consumable_stock_adjustments.createIndex({ consumable_id: 1 });
db.consumable_stock_adjustments.createIndex({ created_by: 1 });
db.consumable_stock_adjustments.createIndex({ reference_type: 1, reference_id: 1 });

db.inventory_maintenance_logs.createIndex({ inventory_item_id: 1 });
db.inventory_maintenance_logs.createIndex({ staf_lab_id: 1 });
db.inventory_maintenance_logs.createIndex({ maintenance_date: 1 });

db.maintenance_consumable_usages.createIndex({ maintenance_log_id: 1 });
db.maintenance_consumable_usages.createIndex({ consumable_id: 1 });