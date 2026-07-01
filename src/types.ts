export type UserRole = 'admin' | 'landlord' | 'tenant';
export type UserStatus = 'active' | 'suspended';

export interface User {
  id: number;
  fullName: string;
  email: string;
  phone: string;
  role: UserRole;
  status: UserStatus;
  profilePhoto?: string;
  createdAt: string;
}

export interface Property {
  id: number;
  landlordId: number;
  name: string;
  address: string;
  county: string;
  propertyType: 'apartment' | 'bedsitter' | 'studio' | 'maisonette' | 'bungalow';
  totalUnits: number;
  description: string;
  amenities: string[];
  photos: string[];
  status: 'active' | 'inactive';
  createdAt: string;
}

export interface Unit {
  id: number;
  propertyId: number;
  unitNumber: string;
  floor: number;
  bedrooms: number;
  bathrooms: number;
  rentAmount: number;
  depositAmount: number;
  status: 'vacant' | 'occupied' | 'maintenance';
  createdAt: string;
}

export interface Tenancy {
  id: number;
  unitId: number;
  tenantId: number;
  landlordId: number;
  startDate: string;
  endDate: string;
  rentAmount: number;
  depositPaid: number;
  depositAmount: number;
  leaseDocument?: string;
  status: 'active' | 'terminated' | 'expired';
  createdAt: string;
}

export interface Payment {
  id: number;
  tenancyId: number;
  tenantId: number;
  landlordId: number;
  amount: number;
  paymentDate: string;
  paymentMethod: 'mpesa' | 'bank' | 'cash';
  mpesaCode?: string;
  receiptNumber: string;
  monthPaidFor: string; // YYYY-MM
  status: 'pending' | 'confirmed' | 'rejected';
  notes?: string;
  createdAt: string;
}

export interface MaintenanceRequest {
  id: number;
  tenancyId: number;
  unitId: number;
  tenantId: number;
  landlordId: number;
  title: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  category: 'plumbing' | 'electrical' | 'structural' | 'cleaning' | 'other';
  status: 'open' | 'in_progress' | 'resolved' | 'closed';
  landlordNotes?: string;
  photos: string[];
  createdAt: string;
  resolvedAt?: string;
}

export interface Notice {
  id: number;
  senderId: number;
  recipientId: number | null; // null for broadcasts
  tenancyId?: number;
  subject: string;
  message: string;
  type: 'rent_reminder' | 'eviction' | 'maintenance' | 'general';
  isRead: boolean;
  createdAt: string;
}

export interface AuditLog {
  id: number;
  userId: number | null;
  action: string;
  description: string;
  ipAddress: string;
  createdAt: string;
}
