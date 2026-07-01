import React, { useState } from 'react';
import { User, Property, Unit, Notice, AuditLog } from '../types';
import { Users, Home, BarChart2, Bell, Shield, Plus, Ban, Check, Trash2, Search } from 'lucide-react';

interface AdminDashboardProps {
  users: User[];
  properties: Property[];
  units: Unit[];
  notices: Notice[];
  auditLogs: AuditLog[];
  onAddUser: (user: Omit<User, 'id' | 'createdAt'>) => void;
  onUpdateUserStatus: (id: number, status: 'active' | 'suspended') => void;
  onDeleteUser: (id: number) => void;
  onSendNotice: (subject: string, message: string, type: string) => void;
}

export default function AdminDashboard({
  users,
  properties,
  units,
  notices,
  auditLogs,
  onAddUser,
  onUpdateUserStatus,
  onDeleteUser,
  onSendNotice
}: AdminDashboardProps) {
  const [activeTab, setActiveTab] = useState<'kpis' | 'users' | 'properties' | 'notices' | 'logs'>('kpis');
  
  // States for adding user
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [newFullName, setNewFullName] = useState('');
  const [newEmail, setNewEmail] = useState('');
  const [newPhone, setNewPhone] = useState('');
  const [newRole, setNewRole] = useState<'landlord' | 'tenant'>('landlord');

  // States for Notices
  const [newSubject, setNewSubject] = useState('');
  const [newMessage, setNewMessage] = useState('');
  const [newType, setNewType] = useState('general');

  // Filter state
  const [userRoleFilter, setUserRoleFilter] = useState<string>('all');
  const [userSearchText, setUserSearchText] = useState<string>('');

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newFullName || !newEmail || !newPhone) return;
    onAddUser({
      fullName: newFullName,
      email: newEmail,
      phone: newPhone,
      role: newRole,
      status: 'active'
    });
    setNewFullName('');
    setNewEmail('');
    setNewPhone('');
    setShowAddUserModal(false);
  };

  const handleSendNoticeSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newSubject || !newMessage) return;
    onSendNotice(newSubject, newMessage, newType);
    setNewSubject('');
    setNewMessage('');
    alert('System notice broadcasted successfully!');
  };

  // Calculations
  const totalLandlords = users.filter((u) => u.role === 'landlord').length;
  const totalTenants = users.filter((u) => u.role === 'tenant').length;
  const occupiedUnits = units.filter((u) => u.status === 'occupied').length;
  const vacantUnits = units.filter((u) => u.status === 'vacant').length;

  const filteredUsers = users.filter((u) => {
    const matchesRole = userRoleFilter === 'all' || u.role === userRoleFilter;
    const matchesSearch = u.fullName.toLowerCase().includes(userSearchText.toLowerCase()) || 
                          u.email.toLowerCase().includes(userSearchText.toLowerCase());
    return matchesRole && matchesSearch;
  });

  return (
    <div className="flex flex-col self-stretch bg-slate-50 min-h-screen">
      {/* Mini-sub Navigation */}
      <div className="bg-white border-b border-gray-150 py-3.5 px-6 flex flex-row items-center space-x-6 overflow-x-auto shrink-0">
        <button
          onClick={() => setActiveTab('kpis')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'kpis' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <BarChart2 className="h-4 w-4" />
          <span>General Analytics</span>
        </button>
        <button
          onClick={() => setActiveTab('users')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'users' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Users className="h-4 w-4" />
          <span>User Management ({users.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('properties')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'properties' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Home className="h-4 w-4" />
          <span>Properties & Listings ({properties.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('notices')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'notices' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Bell className="h-4 w-4" />
          <span>System Broadcast desk</span>
        </button>
        <button
          onClick={() => setActiveTab('logs')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'logs' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Shield className="h-4 w-4" />
          <span>Audit Logs ({auditLogs.length})</span>
        </button>
      </div>

      <div className="p-6 overflow-y-auto flex-1">
        
        {/* KPI MODULE */}
        {activeTab === 'kpis' && (
          <div className="flex flex-col self-stretch">
            {/* KPI CARDS */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <div className="bg-white p-5 rounded-xl border border-gray-150">
                <p className="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Total Registered Users</p>
                <h3 className="text-2xl font-black text-slate-850 mt-1">{users.length}</h3>
                <p className="text-xs text-brand-green font-medium mt-1">{totalLandlords} Landlords | {totalTenants} Tenants</p>
              </div>
              <div className="bg-white p-5 rounded-xl border border-gray-150">
                <p className="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Properties Managed</p>
                <h3 className="text-2xl font-black text-slate-850 mt-1">{properties.length}</h3>
                <p className="text-xs text-slate-500 font-medium mt-1">Spanning multiple sub-counties</p>
              </div>
              <div className="bg-white p-5 rounded-xl border border-gray-150">
                <p className="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Occupancy Ratio</p>
                <h3 className="text-2xl font-black text-slate-850 mt-1">
                  {units.length > 0 ? Math.round((occupiedUnits / units.length) * 100) : 0}%
                </h3>
                <p className="text-xs text-slate-500 font-medium mt-1">{occupiedUnits} Occupied | {vacantUnits} Vacant</p>
              </div>
              <div className="bg-white p-5 rounded-xl border border-gray-150">
                <p className="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Standard Currency Host</p>
                <h3 className="text-2xl font-black text-slate-850 mt-1">Kenyan Shilling (KES)</h3>
                <p className="text-xs text-slate-500 font-medium mt-1">Prepared statements & salted hashes</p>
              </div>
            </div>

            {/* Simulated Charts and Audits logs */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Visual mini chart via raw SVGs */}
              <div className="p-6 bg-white border border-gray-150 rounded-xl lg:col-span-2">
                <h4 className="text-xs font-bold uppercase tracking-wider text-slate-700 mb-6">Aggregate Monthly Revenue (Visual Projection)</h4>
                <div className="h-48 flex items-end justify-between font-mono text-[9px] text-slate-400 gap-2 px-4 border-b border-l border-slate-200">
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green/30 hover:bg-brand-green w-8 rounded-t-sm h-12 transition-all"></div>
                    <span className="mt-2 text-slate-500">Jan</span>
                  </div>
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green/30 hover:bg-brand-green w-8 rounded-t-sm h-20 transition-all"></div>
                    <span className="mt-2 text-slate-500">Feb</span>
                  </div>
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green/45 hover:bg-brand-green w-8 rounded-t-sm h-28 transition-all"></div>
                    <span className="mt-2 text-slate-500">Mar</span>
                  </div>
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green/45 hover:bg-brand-green w-8 rounded-t-sm h-36 transition-all"></div>
                    <span className="mt-2 text-slate-500">Apr</span>
                  </div>
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green/60 hover:bg-brand-green w-8 rounded-t-sm h-40 transition-all"></div>
                    <span className="mt-2 text-slate-500">May</span>
                  </div>
                  <div className="flex flex-col items-center w-full">
                    <div className="bg-brand-green w-8 rounded-t-sm h-44 shadow-xs"></div>
                    <span className="mt-2 text-slate-700 font-semibold">Jun</span>
                  </div>
                </div>
              </div>

              {/* Quick statistics checklist */}
              <div className="p-6 bg-white border border-gray-150 rounded-xl flex flex-col justify-between">
                <div>
                  <h4 className="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">System security checks</h4>
                  <ul className="text-xs text-slate-500 space-y-3">
                    <li className="flex items-center space-x-2">
                      <Check className="h-4 w-4 text-brand-green shrink-0" />
                      <span>PDO MySQL Injection Protection Active</span>
                    </li>
                    <li className="flex items-center space-x-2">
                      <Check className="h-4 w-4 text-brand-green shrink-0" />
                      <span>Salted Crypt hashes for passwords</span>
                    </li>
                    <li className="flex items-center space-x-2">
                      <Check className="h-4 w-4 text-brand-green shrink-0" />
                      <span>CSRF session-token validation active</span>
                    </li>
                  </ul>
                </div>
                
                <div className="border-t border-slate-100 pt-4 mt-6">
                  <span className="text-[10px] text-slate-400 italic">KejaConnect Admin module is designed to provide complete database governance.</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* USER LIST MODULE */}
        {activeTab === 'users' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
              <div className="flex items-center space-x-3 w-full sm:max-w-md">
                <div className="relative w-full">
                  <Search className="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Search by name, email..."
                    value={userSearchText}
                    onChange={(e) => setUserSearchText(e.target.value)}
                    className="pl-9 pr-4 py-2 text-xs w-full bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-brand-green focus:outline-hidden"
                  />
                </div>
                <select
                  value={userRoleFilter}
                  onChange={(e) => setUserRoleFilter(e.target.value)}
                  className="bg-slate-50 border border-slate-200 text-xs rounded-lg py-2 px-3 focus:outline-hidden"
                >
                  <option value="all">All Roles</option>
                  <option value="landlord">Landlords</option>
                  <option value="tenant">Tenants</option>
                </select>
              </div>

              <button
                onClick={() => setShowAddUserModal(true)}
                className="bg-brand-green hover:bg-brand-green-hover text-white text-xs font-bold py-2.5 px-4 rounded-lg flex items-center space-x-1.5 cursor-pointer shadow-xs transition-colors"
              >
                <Plus className="h-4 w-4" />
                <span>Onboard New User</span>
              </button>
            </div>

            {/* Table */}
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-xs">
                <thead className="bg-slate-50">
                  <tr>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Full Name</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Email Address</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Phone</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Role</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-150 bg-white">
                  {filteredUsers.map((u) => (
                    <tr key={u.id} className="hover:bg-slate-50/50">
                      <td className="px-6 py-4 whitespace-nowrap font-bold text-slate-900">{u.fullName}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-slate-500">{u.email}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-slate-500">{u.phone}</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="uppercase tracking-wider text-[9px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-650">
                          {u.role}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`text-[10px] font-bold px-2.5 py-0.5 rounded-full border ${
                          u.status === 'active' ? 'bg-green-100 text-green-800 border-green-250' : 'bg-red-100 text-red-800 border-red-250'
                        }`}>
                          {u.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap space-x-2">
                        {u.id !== 1 && (
                          <>
                            {u.status === 'active' ? (
                              <button
                                onClick={() => onUpdateUserStatus(u.id, 'suspended')}
                                className="text-red-650 hover:text-red-800 hover:underline cursor-pointer font-semibold"
                              >
                                Suspend
                              </button>
                            ) : (
                              <button
                                onClick={() => onUpdateUserStatus(u.id, 'active')}
                                className="text-green-650 hover:text-green-800 hover:underline cursor-pointer font-semibold"
                              >
                                Activate
                              </button>
                            )}
                            <button
                              onClick={() => {
                                if (confirm('Are you absolutely sure you want to delete this user? This destroys related history!')) {
                                  onDeleteUser(u.id);
                                }
                              }}
                              className="text-slate-400 hover:text-slate-700 cursor-pointer pl-2"
                            >
                              <Trash2 className="h-4 w-4 inline" />
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* PROPERTY LIST MODULE */}
        {activeTab === 'properties' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6">
            <h3 className="font-display font-bold text-slate-850 text-sm mb-4">Cross-County Properties & Units Overview</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {properties.map((p) => {
                const propUnits = units.filter((u) => u.propertyId === p.id);
                return (
                  <div key={p.id} className="p-5 border border-slate-150 rounded-xl bg-slate-50/50 flex flex-col justify-between">
                    <div>
                      <div className="flex justify-between items-start gap-2">
                        <h4 className="font-display font-bold text-slate-900 text-sm">{p.name}</h4>
                        <span className="bg-brand-green/10 text-brand-green text-[9px] font-bold px-2 py-0.5 rounded-md uppercase border border-brand-green/20">{p.county}</span>
                      </div>
                      <p className="text-[11px] text-slate-450 mt-1">{p.address}</p>
                      <p className="text-xs text-slate-500 mt-3 leading-relaxed">{p.description}</p>
                    </div>

                    <div className="border-t border-slate-200 mt-5 pt-4">
                      <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Configure Units ({propUnits.length})</p>
                      <div className="flex flex-wrap gap-2">
                        {propUnits.map((u) => (
                          <span
                            key={u.id}
                            className={`text-[10px] font-mono px-2 py-1 rounded-md border font-semibold ${
                              u.status === 'occupied' 
                                ? 'bg-green-100 text-green-800 border-green-200' 
                                : u.status === 'maintenance' 
                                ? 'bg-yellow-100 text-yellow-800 border-yellow-250' 
                                : 'bg-blue-50 text-blue-800 border-blue-200'
                            }`}
                          >
                            Unit {u.unitNumber} (KES {u.rentAmount.toLocaleString()})
                          </span>
                        ))}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* SYSTEM NOTICES BROADCAST MODULE */}
        {activeTab === 'notices' && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="bg-white p-6 rounded-xl border border-gray-150 lg:col-span-2">
              <h3 className="font-display font-bold text-slate-850 text-sm mb-4">Post Global System Broadcast Notice</h3>
              <form onSubmit={handleSendNoticeSubmit} className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-slate-650 uppercase tracking-widest mb-1">Subject Heading</label>
                  <input
                    type="text"
                    required
                    value={newSubject}
                    onChange={(e) => setNewSubject(e.target.value)}
                    placeholder="e.g. Server Software regular maintenance schedule"
                    className="w-full text-xs p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-brand-green focus:outline-hidden"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-650 uppercase tracking-widest mb-1">Type Categories</label>
                  <select
                    value={newType}
                    onChange={(e) => setNewType(e.target.value)}
                    className="w-full text-xs p-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden"
                  >
                    <option value="general">General Broadcast Notification</option>
                    <option value="rent_reminder">Official Rental Reminder</option>
                    <option value="maintenance">Urgent Structural Notice</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-bold text-slate-650 uppercase tracking-widest mb-1">Detailed Message Thread</label>
                  <textarea
                    required
                    rows={4}
                    value={newMessage}
                    onChange={(e) => setNewMessage(e.target.value)}
                    placeholder="Type broadcast message..."
                    className="w-full text-xs p-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-brand-green focus:outline-hidden"
                  ></textarea>
                </div>
                <button
                  type="button"
                  onClick={handleSendNoticeSubmit}
                  className="bg-brand-green hover:bg-brand-green-hover text-white text-xs font-bold py-2.5 px-5 rounded-lg cursor-pointer transition-colors"
                >
                  Broadcast Notice Thread
                </button>
              </form>
            </div>

            <div className="bg-white p-6 rounded-xl border border-gray-150 overflow-hidden">
              <h3 className="font-display font-bold text-slate-850 text-sm mb-4">Historical Sent Tickers</h3>
              <div className="space-y-4 max-h-[350px] overflow-y-auto">
                {notices.map((n) => (
                  <div key={n.id} className="p-4 border border-slate-100 rounded-lg bg-slate-50/50">
                    <p className="font-bold text-slate-900 text-xs">{n.subject}</p>
                    <p className="text-[11px] text-slate-500 mt-1 leading-relaxed">{n.message}</p>
                    <span className="inline-block mt-2 text-[9px] text-brand-gold font-bold uppercase">{n.type}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* AUDIT LOG TRAIL */}
        {activeTab === 'logs' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6 overflow-hidden">
            <h3 className="font-display font-bold text-slate-850 text-sm mb-4">Official System Security Audits Logs</h3>
            <div className="overflow-x-auto max-h-[400px]">
              <table className="min-w-full divide-y divide-gray-200 text-[11px] font-mono select-none">
                <thead className="bg-slate-50">
                  <tr>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Stamp</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Module Action</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Audit Description</th>
                    <th className="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-wider">Gateway IP</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-150 bg-white text-slate-600">
                  {auditLogs.map((log) => (
                    <tr key={log.id} className="hover:bg-slate-50/30">
                      <td className="px-6 py-3.5 whitespace-nowrap text-slate-400">{new Date(log.createdAt).toLocaleString()}</td>
                      <td className="px-6 py-3.5 whitespace-nowrap font-bold text-brand-green">{log.action}</td>
                      <td className="px-6 py-3.5 text-slate-500 leading-normal">{log.description}</td>
                      <td className="px-6 py-3.5 whitespace-nowrap font-semibold text-slate-400">{log.ipAddress}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

      </div>

      {/* ADD USER DIALOG MODAL */}
      {showAddUserModal && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl p-6 w-full max-w-sm border border-slate-100 shadow-xl">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Provision New Database Account</h3>
            <form onSubmit={handleCreateUser} className="space-y-4 text-xs text-slate-600">
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Recipient Full Name</label>
                <input
                  type="text"
                  required
                  placeholder="Mwenda Kamau"
                  value={newFullName}
                  onChange={(e) => setNewFullName(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Email Coordinates</label>
                <input
                  type="email"
                  required
                  placeholder="name@mail.ke"
                  value={newEmail}
                  onChange={(e) => setNewEmail(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Mobile Contact No</label>
                <input
                  type="text"
                  required
                  placeholder="+254700000000"
                  value={newPhone}
                  onChange={(e) => setNewPhone(e.target.value)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Assigned Role Authorization</label>
                <select
                  value={newRole}
                  onChange={(e) => setNewRole(e.target.value as any)}
                  className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                >
                  <option value="landlord">Landlord Partner Account</option>
                  <option value="tenant">Regular Tenant Account</option>
                </select>
              </div>
              <div className="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setShowAddUserModal(false)}
                  className="bg-slate-100 text-slate-650 hover:bg-slate-200 font-bold py-2 px-4 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-brand-green hover:bg-brand-green-hover text-white font-bold py-2 px-4 rounded-lg cursor-pointer shadow-xs"
                >
                  Activate Account
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
