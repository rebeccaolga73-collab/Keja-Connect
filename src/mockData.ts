import { User, Property, Unit, Tenancy, Payment, MaintenanceRequest, Notice, AuditLog } from './types';

export const initialUsers: User[] = [
  {
    id: 1,
    fullName: 'KejaConnect Admin',
    email: 'admin@kejaconnect.co.ke',
    phone: '+254712345678',
    role: 'admin',
    status: 'active',
    createdAt: '2026-01-01T08:00:00Z'
  },
  {
    id: 2,
    fullName: 'Mwenda Joseph',
    email: 'mwenda.landlord@gmail.com',
    phone: '+254722112233',
    role: 'landlord',
    status: 'active',
    createdAt: '2026-01-10T11:30:00Z'
  },
  {
    id: 3,
    fullName: 'Wanjiku Kamau',
    email: 'wanjiku.tenant@yahoo.com',
    phone: '+254733445566',
    role: 'tenant',
    status: 'active',
    createdAt: '2026-02-15T14:20:00Z'
  }
];

export const initialProperties: Property[] = [
  {
    id: 1,
    landlordId: 2,
    name: 'Greenwood Apartments',
    address: 'Ngong Road, near Junction Mall',
    county: 'Nairobi',
    propertyType: 'apartment',
    totalUnits: 2,
    description: 'Luxury modern apartments with fast fiber internet and spacious parking.',
    amenities: ['Fiber Optic', 'High-speed Lift', 'Solar Panels', '24/7 Guards'],
    photos: ['https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80'],
    status: 'active',
    createdAt: '2026-01-15T10:00:00Z'
  },
  {
    id: 2,
    landlordId: 2,
    name: 'Sunset Maisonettes',
    address: 'Section 9',
    county: 'Kiambu',
    propertyType: 'maisonette',
    totalUnits: 1,
    description: 'Cozy gated community units ideal for growing families.',
    amenities: ['Borehole', 'Private Garden', 'Gated', 'Gym'],
    photos: ['https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'],
    status: 'active',
    createdAt: '2026-02-01T09:00:00Z'
  }
];

export const initialUnits: Unit[] = [
  {
    id: 1,
    propertyId: 1,
    unitNumber: 'A101',
    floor: 0,
    bedrooms: 2,
    bathrooms: 2,
    rentAmount: 45000,
    depositAmount: 45000,
    status: 'occupied',
    createdAt: '2026-01-15T12:00:00Z'
  },
  {
    id: 2,
    propertyId: 1,
    unitNumber: 'A102',
    floor: 1,
    bedrooms: 3,
    bathrooms: 3,
    rentAmount: 55000,
    depositAmount: 55000,
    status: 'vacant',
    createdAt: '2026-01-15T12:00:00Z'
  },
  {
    id: 3,
    propertyId: 2,
    unitNumber: 'M01',
    floor: 0,
    bedrooms: 4,
    bathrooms: 4,
    rentAmount: 85000,
    depositAmount: 85000,
    status: 'vacant',
    createdAt: '2026-02-01T11:00:00Z'
  }
];

export const initialTenancies: Tenancy[] = [
  {
    id: 1,
    unitId: 1,
    tenantId: 3,
    landlordId: 2,
    startDate: '2026-01-01',
    endDate: '2026-12-31',
    rentAmount: 45000,
    depositPaid: 45000,
    depositAmount: 45000,
    leaseDocument: 'uploads/documents/keja_sample_lease.pdf',
    status: 'active',
    createdAt: '2026-01-01T09:00:00Z'
  }
];

export const initialPayments: Payment[] = [
  {
    id: 1,
    tenancyId: 1,
    tenantId: 3,
    landlordId: 2,
    amount: 45000,
    paymentDate: '2026-05-01',
    paymentMethod: 'mpesa',
    mpesaCode: 'QRE3YHJ9KK',
    receiptNumber: 'REC-2026-0001',
    monthPaidFor: '2026-05',
    status: 'confirmed',
    notes: 'Paid on time.',
    createdAt: '2026-05-01T10:15:00Z'
  },
  {
    id: 2,
    tenancyId: 1,
    tenantId: 3,
    landlordId: 2,
    amount: 45000,
    paymentDate: '2026-06-02',
    paymentMethod: 'mpesa',
    mpesaCode: 'QRF4YHN8ML',
    receiptNumber: 'REC-2026-0002',
    monthPaidFor: '2026-06',
    status: 'pending',
    notes: 'Please approve.',
    createdAt: '2026-06-02T16:45:00Z'
  }
];

export const initialMaintenance: MaintenanceRequest[] = [
  {
    id: 1,
    tenancyId: 1,
    unitId: 1,
    tenantId: 3,
    landlordId: 2,
    title: 'Leaking kitchen tap',
    description: 'The main sink mixer tap is dripping continuously, wasting water and filling the under-sink cupboard.',
    priority: 'medium',
    category: 'plumbing',
    status: 'open',
    photos: [],
    createdAt: '2026-06-02T11:20:00Z'
  }
];

export const initialNotices: Notice[] = [
  {
    id: 1,
    senderId: 1,
    recipientId: null,
    subject: 'System Launch',
    message: 'Welcome to the KejaConnect real-estate system!',
    type: 'general',
    isRead: false,
    createdAt: '2026-06-01T09:00:00Z'
  },
  {
    id: 2,
    senderId: 2,
    recipientId: 3,
    tenancyId: 1,
    subject: 'Water Shortage Warning',
    message: 'Water supply will be rationalized in Greenwood Apartments from 10 AM to 4 PM on Wednesday for regular tank maintenance.',
    type: 'general',
    isRead: false,
    createdAt: '2026-06-03T14:30:00Z'
  }
];

export const initialAuditLogs: AuditLog[] = [
  {
    id: 1,
    userId: null,
    action: 'system_initialize',
    description: 'KejaConnect Property Hub Sandbox active',
    ipAddress: '127.0.0.1',
    createdAt: '2026-06-07T12:00:00Z'
  }
];

// Helper to load or initialize from localStorage
export function loadFromLocalStorage<T>(key: string, defaultValue: T): T {
  try {
    const saved = localStorage.getItem(`kejaconnect_${key}`);
    return saved ? JSON.parse(saved) : defaultValue;
  } catch (e) {
    return defaultValue;
  }
}

// Helper to save to localStorage
export function saveToLocalStorage<T>(key: string, value: T): void {
  try {
    localStorage.setItem(`kejaconnect_${key}`, JSON.stringify(value));
  } catch (e) {
    console.error(`Failed to preserve ${key}`, e);
  }
}
