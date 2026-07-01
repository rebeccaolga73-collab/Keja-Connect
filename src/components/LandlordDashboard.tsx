import React, { useState } from 'react';
import { Property, Unit, Tenancy, Payment, MaintenanceRequest, User } from '../types';
import { Home, Plus, Trash2, Check, Ban, Eye, Activity, FileText } from 'lucide-react';

interface LandlordDashboardProps {
  properties: Property[];
  units: Unit[];
  tenancies: Tenancy[];
  payments: Payment[];
  maintenance: MaintenanceRequest[];
  tenants: User[];
  onAddProperty: (prop: Omit<Property, 'id' | 'landlordId' | 'status' | 'createdAt'>) => void;
  onDeleteProperty: (id: number) => void;
  onAddUnit: (propertyId: number, unit: Omit<Unit, 'id' | 'propertyId' | 'createdAt'>) => void;
  onAssignTenant: (unitId: number, tenantEmail: string) => void;
  onTerminateTenancy: (tenancyId: number) => void;
  onProcessPayment: (id: number, status: 'confirmed' | 'rejected') => void;
  onResolveMaintenance: (id: number, notes: string, status: 'in_progress' | 'resolved' | 'closed') => void;
  onViewReceipt: (payment: Payment) => void;
}

export default function LandlordDashboard({
  properties,
  units,
  tenancies,
  payments,
  maintenance,
  tenants,
  onAddProperty,
  onDeleteProperty,
  onAddUnit,
  onAssignTenant,
  onTerminateTenancy,
  onProcessPayment,
  onResolveMaintenance,
  onViewReceipt
}: LandlordDashboardProps) {
  const [activeTab, setActiveTab] = useState<'listings' | 'payments' | 'maintenance' | 'tenants'>('listings');

  // Modal / Inputs state
  const [showAddPropModal, setShowAddPropModal] = useState(false);
  const [propName, setPropName] = useState('');
  const [propAddress, setPropAddress] = useState('');
  const [propCounty, setPropCounty] = useState('Nairobi');
  const [propType, setPropType] = useState<'apartment' | 'bedsitter' | 'studio' | 'maisonette' | 'bungalow'>('apartment');
  const [propDesc, setPropDesc] = useState('');

  // Add unit state
  const [selectedPropIdForUnit, setSelectedPropIdForUnit] = useState<number | null>(null);
  const [unitNumber, setUnitNumber] = useState('');
  const [unitFloor, setUnitFloor] = useState(0);
  const [unitBedrooms, setUnitBedrooms] = useState(1);
  const [unitBathrooms, setUnitBathrooms] = useState(1);
  const [unitRent, setUnitRent] = useState(25000);

  // Match tenant
  const [selectedUnitIdForTenant, setSelectedUnitIdForTenant] = useState<number | null>(null);
  const [tenantEmail, setTenantEmail] = useState('');

  // Maintenance Reply states
  const [selectedMaintId, setSelectedMaintId] = useState<number | null>(null);
  const [landlordReplyNotes, setLandlordReplyNotes] = useState('');

  const handleCreateProperty = (e: React.FormEvent) => {
    e.preventDefault();
    if (!propName || !propAddress) return;
    onAddProperty({
      name: propName,
      address: propAddress,
      county: propCounty,
      propertyType: propType,
      totalUnits: 0,
      description: propDesc,
      amenities: ['Borehole', 'Fiber Ready'],
      photos: ['https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80']
    });
    setPropName('');
    setPropAddress('');
    setPropDesc('');
    setShowAddPropModal(false);
  };

  const handleCreateUnit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPropIdForUnit || !unitNumber) return;
    onAddUnit(selectedPropIdForUnit, {
      unitNumber,
      floor: Number(unitFloor),
      bedrooms: Number(unitBedrooms),
      bathrooms: Number(unitBathrooms),
      rentAmount: Number(unitRent),
      depositAmount: Number(unitRent),
      status: 'vacant'
    });
    setUnitNumber('');
    setSelectedPropIdForUnit(null);
  };

  const handleMatchTenant = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUnitIdForTenant || !tenantEmail) return;
    onAssignTenant(selectedUnitIdForTenant, tenantEmail);
    setTenantEmail('');
    setSelectedUnitIdForTenant(null);
  };

  const handleMaintenanceUpdate = (id: number, status: 'in_progress' | 'resolved' | 'closed') => {
    onResolveMaintenance(id, landlordReplyNotes || 'Landlord checked report details.', status);
    setLandlordReplyNotes('');
    setSelectedMaintId(null);
  };

  // Calculations
  const grossCollected = payments
    .filter((p) => p.status === 'confirmed')
    .reduce((sum, p) => sum + p.amount, 0);

  const pendingCount = payments.filter((p) => p.status === 'pending').length;

  return (
    <div className="flex flex-col self-stretch bg-slate-50 min-h-screen">
      {/* Landlord Sub Navigation */}
      <div className="bg-white border-b border-gray-150 py-3.5 px-6 flex flex-row items-center space-x-6 overflow-x-auto shrink-0">
        <button
          onClick={() => setActiveTab('listings')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'listings' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Home className="h-4 w-4" />
          <span>My Listings & Units ({properties.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('payments')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'payments' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <FileText className="h-4 w-4" />
          <span>M-Pesa Ledger ({payments.length})</span>
          {pendingCount > 0 && (
            <span className="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full z-10">{pendingCount}</span>
          )}
        </button>
        <button
          onClick={() => setActiveTab('maintenance')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'maintenance' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Plus className="h-4 w-4 text-brand-gold" />
          <span>Tenant Maintenance Claims ({maintenance.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('tenants')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'tenants' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Eye className="h-4 w-4" />
          <span>Active Tenancies ({tenancies.length})</span>
        </button>
      </div>

      <div className="p-6 overflow-y-auto flex-1 text-slate-700">
        {/* LANDLORD SUMMARY KPIS */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 uppercase tracking-widest text-[9px] font-bold text-slate-450">
          <div className="bg-white p-5 rounded-xl border border-gray-150">
            <p>Listings</p>
            <h3 className="text-2xl font-black text-slate-850 tracking-normal mt-1">{properties.length} Estates</h3>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-150">
            <p>Total Units</p>
            <h3 className="text-2xl font-black text-slate-850 tracking-normal mt-1">
              {units.length} rooms ({units.filter((u) => u.status === 'occupied').length} Occupied)
            </h3>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-150">
            <p>Gross Collected</p>
            <h3 className="text-2xl font-black text-brand-green tracking-normal mt-1">KES {grossCollected.toLocaleString()}</h3>
          </div>
          <div className="bg-white p-5 rounded-xl border border-gray-150">
            <p>Pending Review</p>
            <h3 className="text-2xl font-black text-red-650 tracking-normal mt-1">{pendingCount} Payments</h3>
          </div>
        </div>

        {/* LISTINGS TAB */}
        {activeTab === 'listings' && (
          <div className="flex flex-col self-stretch">
            <div className="flex justify-between items-center mb-6">
              <h3 className="font-display font-bold text-slate-900 text-sm">Property Management</h3>
              <button
                onClick={() => setShowAddPropModal(true)}
                className="bg-brand-green hover:bg-brand-green-hover text-white text-xs font-bold py-2.5 px-4 rounded-lg flex items-center space-x-1 cursor-pointer"
              >
                <Plus className="h-4 w-4" />
                <span>Add Property Block</span>
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {properties.map((p) => {
                const propUnits = units.filter((u) => u.propertyId === p.id);
                const hasActiveLease = tenancies.some((t) => propUnits.some((u) => u.id === t.unitId) && t.status === 'active');

                return (
                  <div key={p.id} className="bg-white border border-gray-150 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                    <div>
                      <div className="flex justify-between items-start gap-4">
                        <div>
                          <h4 className="font-display font-bold text-slate-900 text-sm">{p.name}</h4>
                          <span className="text-[10px] text-slate-400 block font-medium mt-0.5">{p.address}, <span className="text-brand-green font-bold">{p.county}</span></span>
                        </div>
                        
                        <button
                          onClick={() => {
                            if (hasActiveLease) {
                              alert('This property cannot be deleted since it stores active tenant leases.');
                            } else if (confirm('Are you sure you want to delete this property listing?')) {
                              onDeleteProperty(p.id);
                            }
                          }}
                          className={`p-2 rounded-lg cursor-pointer ${
                            hasActiveLease ? 'text-gray-300' : 'text-slate-400 hover:text-red-600 hover:bg-red-50'
                          }`}
                          title="Delete Property"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>

                      <p className="text-xs text-slate-500 mt-4 leading-relaxed">{p.description}</p>
                    </div>

                    <div className="border-t border-slate-100 mt-6 pt-5">
                      <div className="flex justify-between items-center mb-3">
                        <span className="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Listed Rooms/Units ({propUnits.length})</span>
                        <button
                          onClick={() => setSelectedPropIdForUnit(p.id)}
                          className="text-[11px] font-bold text-brand-green hover:text-brand-green-hover flex items-center space-x-0.5"
                        >
                          <Plus className="h-3.5 w-3.5" />
                          <span>Add Unit Form</span>
                        </button>
                      </div>

                      <div className="space-y-2.5">
                        {propUnits.map((u) => {
                          const activeLease = tenancies.find((ten) => ten.unitId === u.id && ten.status === 'active');
                          const activeTenantUser = activeLease ? tenants.find((us) => us.id === activeLease.tenantId) : null;

                          return (
                            <div key={u.id} className="p-3 bg-slate-50 border border-slate-150 rounded-lg flex items-center justify-between text-xs">
                              <div>
                                <span className="font-bold font-mono text-slate-800">Unit {u.unitNumber}</span>
                                <span className="text-slate-400 text-[10px] block font-semibold mt-0.5">Rent: KES {u.rentAmount.toLocaleString()} | {u.bedrooms}BR | Floor {u.floor}</span>
                              </div>

                              <div>
                                {activeTenantUser ? (
                                  <div className="text-right">
                                    <span className="bg-green-150 text-green-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">Occupied</span>
                                    <span className="text-[10px] text-slate-450 block mt-0.5">By: {activeTenantUser.fullName}</span>
                                  </div>
                                ) : (
                                  <button
                                    onClick={() => setSelectedUnitIdForTenant(u.id)}
                                    className="bg-brand-gold hover:bg-brand-gold-hover text-white text-[10px] font-bold px-3 py-1.5 rounded-md cursor-pointer transition-colors"
                                  >
                                    Assign Tenant
                                  </button>
                                )}
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* LEDGER TAB */}
        {activeTab === 'payments' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Pending and Confirmed Tenant Clearances</h3>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-xs">
                <thead className="bg-slate-50 text-slate-500">
                  <tr>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Date</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Tenant</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Month Settled</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">M-Pesa reference</th>
                    <th className="px-6 py-3 text-right font-bold uppercase tracking-wider">Amount</th>
                    <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Status</th>
                    <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Action Commands</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-150 bg-white">
                  {payments.map((p) => {
                    const payTenant = tenants.find((us) => us.id === p.tenantId);
                    return (
                      <tr key={p.id}>
                        <td className="px-6 py-4 whitespace-nowrap text-slate-400">{p.paymentDate}</td>
                        <td className="px-6 py-4 whitespace-nowrap font-bold text-slate-800">{payTenant ? payTenant.fullName : 'Guest'}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-slate-550">{p.monthPaidFor}</td>
                        <td className="px-6 py-4 whitespace-nowrap font-mono text-brand-green font-bold">{p.mpesaCode || 'CASH/BANK'}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-right font-black text-slate-900">KES {p.amount.toLocaleString()}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`text-[9px] font-bold px-2 py-0.5 rounded-full border ${
                            p.status === 'confirmed' ? 'bg-green-100 text-green-800 border-green-200' : p.status === 'rejected' ? 'bg-red-105 text-red-800 border-red-200' : 'bg-yellow-100 text-yellow-805 border-yellow-200'
                          }`}>
                            {p.status}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center space-x-2">
                          {p.status === 'pending' ? (
                            <>
                              <button
                                onClick={() => onProcessPayment(p.id, 'confirmed')}
                                className="bg-brand-green hover:bg-brand-green-hover text-white text-[10px] font-bold px-3 py-1 rounded-md cursor-pointer"
                              >
                                Approve M-Pesa
                              </button>
                              <button
                                onClick={() => onProcessPayment(p.id, 'rejected')}
                                className="bg-slate-100 text-slate-650 hover:bg-slate-200 text-[10px] font-bold px-3 py-1 rounded-md cursor-pointer"
                              >
                                Reject
                              </button>
                            </>
                          ) : p.status === 'confirmed' ? (
                            <button
                              onClick={() => onViewReceipt(p)}
                              className="text-brand-green hover:underline font-bold text-[10px]"
                            >
                              Show PDF Receipt
                            </button>
                          ) : (
                            <span className="text-slate-400">Rejected</span>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* MAINTENANCE CLAIMS TAB */}
        {activeTab === 'maintenance' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6 text-xs text-slate-700">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Urgent Maintenance Claims from Tenants</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {maintenance.map((m) => {
                const claimTenant = tenants.find((us) => us.id === m.tenantId);
                const claimPropUnit = units.find((u) => u.id === m.unitId);
                const claimProp = claimPropUnit ? properties.find((pr) => pr.id === claimPropUnit.propertyId) : null;

                return (
                  <div key={m.id} className="p-5 border border-slate-150 rounded-xl bg-slate-50/50 flex flex-col justify-between">
                    <div>
                      <div className="flex justify-between items-start gap-4">
                        <div>
                          <span className={`text-[9px] font-bold tracking-widest uppercase px-2 py-0.5 rounded-full border ${
                            m.priority === 'urgent' || m.priority === 'high' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-sky-50 text-sky-800 border-sky-200'
                          }`}>{m.priority} Priority</span>
                          <h4 className="font-display font-bold text-slate-900 text-sm mt-3">{m.title}</h4>
                        </div>
                        <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                          m.status === 'resolved' || m.status === 'closed' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-101 text-yellow-850 border-yellow-250'
                        }`}>{m.status}</span>
                      </div>

                      <p className="text-xs text-slate-500 mt-3 leading-relaxed">{m.description}</p>
                      
                      <div className="mt-4 p-3 bg-white border border-slate-100 rounded-lg">
                        <p className="text-[10px] text-slate-400 font-bold uppercase leading-none">Reference Scope</p>
                        <p className="text-slate-800 font-bold text-xs mt-1">Tenant: {claimTenant ? claimTenant.fullName : 'Wanjiku Kamau'}</p>
                        <p className="text-slate-500 mt-0.5">{claimProp ? claimProp.name : 'Greenwood'} — Unit {claimPropUnit ? claimPropUnit.unitNumber : 'A101'}</p>
                      </div>

                      {m.landlordNotes && (
                        <div className="mt-4 p-3 bg-brand-green/5 border border-brand-green/10 rounded-lg text-brand-green">
                          <p className="text-[10px] font-extrabold uppercase leading-none">Landlord Response</p>
                          <p className="mt-1 font-semibold">{m.landlordNotes}</p>
                        </div>
                      )}
                    </div>

                    {m.status !== 'resolved' && (
                      <div className="border-t border-slate-200 mt-6 pt-4">
                        <p className="text-[10px] font-extrabold uppercase tracking-wide text-slate-450 mb-2">Respond / Update Status</p>
                        <div className="flex items-center space-x-2">
                          <input
                            type="text"
                            placeholder="Add progress log response..."
                            value={selectedMaintId === m.id ? landlordReplyNotes : ''}
                            onChange={(e) => {
                              setSelectedMaintId(m.id);
                              setLandlordReplyNotes(e.target.value);
                            }}
                            className="bg-white border border-slate-200 rounded-md py-1.5 px-3 flex-1 text-xs focus:outline-hidden"
                          />
                          <button
                            onClick={() => handleMaintenanceUpdate(m.id, 'resolved')}
                            className="bg-brand-green hover:bg-brand-green-hover text-white py-1.5 px-3 rounded-md font-bold text-xs cursor-pointer shadow-xs"
                          >
                            Mark Handled
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* TENANTS TAB */}
        {activeTab === 'tenants' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6 text-xs text-slate-700">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Active Tenancy Contracts</h3>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-xs text-slate-650">
                <thead className="bg-slate-50 text-slate-500">
                  <tr>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Tenant Name</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Assigned Unit</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Start Date</th>
                    <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">End Date</th>
                    <th className="px-6 py-3 text-right font-bold uppercase tracking-wider">Rent Rate</th>
                    <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Status</th>
                    <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-150 bg-white">
                  {tenancies.map((t) => {
                    const tenTenant = tenants.find((us) => us.id === t.tenantId);
                    const tenUnit = units.find((u) => u.id === t.unitId);
                    return (
                      <tr key={t.id}>
                        <td className="px-6 py-4 whitespace-nowrap font-bold text-slate-800">{tenTenant ? tenTenant.fullName : 'Guest'}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-slate-500 font-mono">Unit {tenUnit ? tenUnit.unitNumber : 'Unknown'}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-slate-400">{t.startDate}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-slate-400">{t.endDate}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-905">KES {t.rentAmount.toLocaleString()}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                            t.status === 'active' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200'
                          }`}>{t.status}</span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          {t.status === 'active' && (
                            <button
                              onClick={() => {
                                if (confirm('Are you sure you want to terminate this tenancy? This marks the room vacant instantly!')) {
                                  onTerminateTenancy(t.id);
                                }
                              }}
                              className="text-red-650 hover:text-red-800 hover:underline cursor-pointer font-bold duration-200"
                            >
                              Terminate
                            </button>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}

      </div>

      {/* ADD PROPERTY BLOCK MODAL */}
      {showAddPropModal && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-xs">
          <div className="bg-white rounded-xl p-6 w-full max-w-sm border border-slate-100 shadow-xl text-xs text-slate-600">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Register New Property Block</h3>
            <form onSubmit={handleCreateProperty} className="space-y-4">
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Estate Name</label>
                <input
                  type="text"
                  required
                  placeholder="Greenwood Ridge Apartments"
                  value={propName}
                  onChange={(e) => setPropName(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">County Region</label>
                <select
                  value={propCounty}
                  onChange={(e) => setPropCounty(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                >
                  <option value="Nairobi">Nairobi</option>
                  <option value="Kiambu">Kiambu</option>
                  <option value="Mombasa">Mombasa</option>
                  <option value="Kisumu">Kisumu</option>
                  <option value="Nakuru">Nakuru</option>
                </select>
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Physical Street Address</label>
                <input
                  type="text"
                  required
                  placeholder="Ngong Road, near Junction"
                  value={propAddress}
                  onChange={(e) => setPropAddress(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Structural Category</label>
                <select
                  value={propType}
                  onChange={(e) => setPropType(e.target.value as any)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                >
                  <option value="apartment">Modern Apartment</option>
                  <option value="bedsitter">Economic Bedsitter</option>
                  <option value="studio">Cozy Studio Room</option>
                  <option value="maisonette">Gated Maisonette</option>
                  <option value="bungalow">Family Bungalow</option>
                </select>
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Description Profile</label>
                <textarea
                  placeholder="Tell tenants about borehole, luxury items..."
                  value={propDesc}
                  rows={2}
                  onChange={(e) => setPropDesc(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                ></textarea>
              </div>
              <div className="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setShowAddPropModal(false)}
                  className="bg-slate-100 text-slate-650 hover:bg-slate-200 font-bold py-2 px-4 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-brand-green hover:bg-brand-green-hover text-white font-bold py-2 px-4 rounded-lg cursor-pointer shadow-xs"
                >
                  Create Listing
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ADD UNIT INPUT SLIDE OVER / DIALOG */}
      {selectedPropIdForUnit !== null && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-xs">
          <div className="bg-white rounded-xl p-6 w-full max-w-sm border border-slate-100 shadow-xl text-xs text-slate-600">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Add Unit to Estate</h3>
            <form onSubmit={handleCreateUnit} className="space-y-4">
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Unit / Room Number</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. A102"
                  value={unitNumber}
                  onChange={(e) => setUnitNumber(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Floor Level</label>
                  <input
                    type="number"
                    value={unitFloor}
                    onChange={(e) => setUnitFloor(Number(e.target.value))}
                    className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Monthly Rent</label>
                  <input
                    type="number"
                    value={unitRent}
                    onChange={(e) => setUnitRent(Number(e.target.value))}
                    className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  />
                </div>
              </div>
              <div className="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setSelectedPropIdForUnit(null)}
                  className="bg-slate-100 text-slate-650 hover:bg-slate-200 font-bold py-2 px-4 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-brand-green hover:bg-brand-green-hover text-white font-bold py-2 px-4 rounded-lg shadow-xs"
                >
                  Register Room
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ASSIGN TENANT MODAL */}
      {selectedUnitIdForTenant !== null && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-xs">
          <div className="bg-white rounded-xl p-6 w-full max-w-sm border border-slate-100 shadow-xl text-xs text-slate-600">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-1">Match Tenant to Room</h3>
            <p className="text-[10px] text-slate-400 mb-4 font-semibold">Uses email database lookup, e.g. <span className="text-brand-green">wanjiku.tenant@yahoo.com</span></p>
            <form onSubmit={handleMatchTenant} className="space-y-4">
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Registered Tenant Email</label>
                <input
                  type="email"
                  required
                  placeholder="e.g. wanjiku.tenant@yahoo.com"
                  value={tenantEmail}
                  onChange={(e) => setTenantEmail(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden font-semibold"
                />
              </div>
              <div className="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setSelectedUnitIdForTenant(null)}
                  className="bg-slate-100 text-slate-650 hover:bg-slate-200 font-bold py-2 px-4 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-brand-green hover:bg-brand-green-hover text-white font-bold py-2 px-4 rounded-lg cursor-pointer shadow-xs"
                >
                  Establish Tenancy Lease
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
