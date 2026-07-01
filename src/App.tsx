import React, { useState, useEffect } from 'react';
import { User, Property, Unit, Tenancy, Payment, MaintenanceRequest, Notice, AuditLog } from './types';
import * as mockSeeds from './mockData';
import LandingHero from './components/LandingHero';
import ReceiptModal from './components/ReceiptModal';
import AdminDashboard from './components/AdminDashboard';
import LandlordDashboard from './components/LandlordDashboard';
import TenantDashboard from './components/TenantDashboard';
import SourceCodeViewer from './components/SourceCodeViewer';
import { Home, LogOut, Code, UserCheck, ShieldCheck, Mail, Lock } from 'lucide-react';

export default function App() {
  // Global States synced with LocalStorage
  const [users, setUsers] = useState<User[]>(() => 
    mockSeeds.loadFromLocalStorage('users', mockSeeds.initialUsers)
  );
  const [properties, setProperties] = useState<Property[]>(() => 
    mockSeeds.loadFromLocalStorage('properties', mockSeeds.initialProperties)
  );
  const [units, setUnits] = useState<Unit[]>(() => 
    mockSeeds.loadFromLocalStorage('units', mockSeeds.initialUnits)
  );
  const [tenancies, setTenancies] = useState<Tenancy[]>(() => 
    mockSeeds.loadFromLocalStorage('tenancies', mockSeeds.initialTenancies)
  );
  const [payments, setPayments] = useState<Payment[]>(() => 
    mockSeeds.loadFromLocalStorage('payments', mockSeeds.initialPayments)
  );
  const [maintenance, setMaintenance] = useState<MaintenanceRequest[]>(() => 
    mockSeeds.loadFromLocalStorage('maintenance', mockSeeds.initialMaintenance)
  );
  const [notices, setNotices] = useState<Notice[]>(() => 
    mockSeeds.loadFromLocalStorage('notices', mockSeeds.initialNotices)
  );
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>(() => 
    mockSeeds.loadFromLocalStorage('auditLogs', mockSeeds.initialAuditLogs)
  );

  // Auth states
  const [currentUser, setCurrentUser] = useState<User | null>(null);
  const [showLogin, setShowLogin] = useState(false);
  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');
  const [forgotEmail, setForgotEmail] = useState('');
  const [showForgot, setShowForgot] = useState(false);

  // Active overlays
  const [activeReceipt, setActiveReceipt] = useState<Payment | null>(null);
  const [showSourceCode, setShowSourceCode] = useState(false);

  // Sync state mutations automatically to local storage
  useEffect(() => { mockSeeds.saveToLocalStorage('users', users); }, [users]);
  useEffect(() => { mockSeeds.saveToLocalStorage('properties', properties); }, [properties]);
  useEffect(() => { mockSeeds.saveToLocalStorage('units', units); }, [units]);
  useEffect(() => { mockSeeds.saveToLocalStorage('tenancies', tenancies); }, [tenancies]);
  useEffect(() => { mockSeeds.saveToLocalStorage('payments', payments); }, [payments]);
  useEffect(() => { mockSeeds.saveToLocalStorage('maintenance', maintenance); }, [maintenance]);
  useEffect(() => { mockSeeds.saveToLocalStorage('notices', notices); }, [notices]);
  useEffect(() => { mockSeeds.saveToLocalStorage('auditLogs', auditLogs); }, [auditLogs]);

  // Logging utility helper
  const addAuditLog = (action: string, description: string) => {
    const newLog: AuditLog = {
      id: Date.now(),
      userId: currentUser ? currentUser.id : null,
      action,
      description,
      ipAddress: '192.168.100.15',
      createdAt: new Date().toISOString()
    };
    setAuditLogs(prev => [newLog, ...prev]);
  };

  // Login handler
  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanEmail = loginEmail.trim().toLowerCase();
    
    // Attempt credentials verification
    const foundUser = users.find(u => u.email.toLowerCase() === cleanEmail);
    if (!foundUser) {
      alert('Authentication failure: Email coordinates not registered.');
      return;
    }

    if (foundUser.status === 'suspended') {
      alert('Security halt: This account has been suspended by the platform administrator.');
      return;
    }

    // Bypass verification bounds for sandbox simulation
    setCurrentUser(foundUser);
    setShowLogin(false);
    setLoginEmail('');
    setLoginPassword('');
    
    // Add audit entry
    const newAudit: AuditLog = {
      id: Date.now(),
      userId: foundUser.id,
      action: 'user_login',
      description: `Authenticated successfully as: ${foundUser.fullName} (${foundUser.role})`,
      ipAddress: '192.168.10.22',
      createdAt: new Date().toISOString()
    };
    setAuditLogs(prev => [newAudit, ...prev]);
  };

  const executeQuickLogin = (role: 'admin' | 'landlord' | 'tenant') => {
    const target = users.find(u => u.role === role);
    if (target) {
      setCurrentUser(target);
      setShowLogin(false);
      
      const newAudit: AuditLog = {
        id: Date.now(),
        userId: target.id,
        action: 'quick_login_sandbox',
        description: `Bypassed passwords using sandbox gateway as: ${target.fullName}`,
        ipAddress: '127.0.0.1',
        createdAt: new Date().toISOString()
      };
      setAuditLogs(prev => [newAudit, ...prev]);
    }
  };

  const handleLogout = () => {
    if (currentUser) {
      addAuditLog('user_logout', `Session cleared securely for user: ${currentUser.fullName}`);
    }
    setCurrentUser(null);
  };

  // ADMIN OPERATIONS
  const handleAddUser = (newU: Omit<User, 'id' | 'createdAt'>) => {
    const newUser: User = {
      ...newU,
      id: Date.now(),
      createdAt: new Date().toISOString()
    };
    setUsers(prev => [...prev, newUser]);
    addAuditLog('create_user', `Provisioned new platform account: ${newUser.fullName} (${newUser.role})`);
  };

  const handleUpdateUserStatus = (id: number, status: 'active' | 'suspended') => {
    setUsers(prev => prev.map(u => u.id === id ? { ...u, status } : u));
    const targetUser = users.find(u => u.id === id);
    addAuditLog('update_user_status', `Modified clearance for user [${targetUser?.fullName}]: ${status}`);
    
    // If we suspended the currently active simulation user, log out immediately
    if (currentUser && currentUser.id === id && status === 'suspended') {
      setCurrentUser(null);
      alert('Your session holds suspended credentials. Logout triggered.');
    }
  };

  const handleDeleteUser = (id: number) => {
    const targetUser = users.find(u => u.id === id);
    setUsers(prev => prev.filter(u => u.id !== id));
    addAuditLog('delete_user', `Destroyed platform account and records for: ${targetUser?.fullName}`);
  };

  const handleSendNotice = (subject: string, message: string, type: string) => {
    const newNotice: Notice = {
      id: Date.now(),
      senderId: currentUser ? currentUser.id : 1,
      recipientId: null, // Broadcast
      subject,
      message,
      type: type as any,
      isRead: false,
      createdAt: new Date().toISOString()
    };
    setNotices(prev => [newNotice, ...prev]);
    addAuditLog('broadcast_notice', `Posted system announcement ticker: "${subject}"`);
  };

  // LANDLORD OPERATIONS
  const handleAddProperty = (newP: Omit<Property, 'id' | 'landlordId' | 'status' | 'createdAt'>) => {
    const newProp: Property = {
      ...newP,
      id: Date.now(),
      landlordId: currentUser ? currentUser.id : 2,
      status: 'active',
      createdAt: new Date().toISOString()
    };
    setProperties(prev => [...prev, newProp]);
    addAuditLog('create_property', `Listed new property coordinates: ${newProp.name} at ${newProp.address}`);
  };

  const handleDeleteProperty = (id: number) => {
    const target = properties.find(p => p.id === id);
    setProperties(prev => prev.filter(p => p.id !== id));
    addAuditLog('delete_property', `Scrapped listing: "${target?.name}"`);
  };

  const handleAddUnit = (propertyId: number, newU: Omit<Unit, 'id' | 'propertyId' | 'createdAt'>) => {
    const newUnit: Unit = {
      ...newU,
      id: Date.now(),
      propertyId,
      createdAt: new Date().toISOString()
    };
    setUnits(prev => [...prev, newUnit]);
    
    const propName = properties.find(p => p.id === propertyId)?.name;
    addAuditLog('create_unit', `Attached rental unit ${newUnit.unitNumber} to estate "${propName}" with baseline KES ${newUnit.rentAmount}`);
  };

  const handleAssignTenant = (unitId: number, tEmail: string) => {
    const cleanMail = tEmail.trim().toLowerCase();
    const matchedUser = users.find(u => u.email.toLowerCase() === cleanMail && u.role === 'tenant');
    if (!matchedUser) {
      alert('No tenant found in system database with that email. Ensure they are onboarded first via directory management!');
      return;
    }

    // Check if tenant has an active lease
    const hasActiveLease = tenancies.some(t => t.tenantId === matchedUser.id && t.status === 'active');
    if (hasActiveLease) {
      alert('Conflict: This tenant is already registered with another active rental lease contract.');
      return;
    }

    // Create Tenancy
    const targetUnit = units.find(u => u.id === unitId);
    if (!targetUnit) return;

    const newTenancy: Tenancy = {
      id: Date.now(),
      unitId,
      tenantId: matchedUser.id,
      landlordId: currentUser ? currentUser.id : 2,
      startDate: new Date().toISOString().split('T')[0],
      endDate: new Date(Date.now() + 365*24*60*60*1000).toISOString().split('T')[0], // 1yr
      rentAmount: targetUnit.rentAmount,
      depositAmount: targetUnit.depositAmount,
      depositPaid: targetUnit.depositAmount,
      status: 'active',
      createdAt: new Date().toISOString()
    };

    setTenancies(prev => [...prev, newTenancy]);
    // Set unit as occupied
    setUnits(prev => prev.map(u => u.id === unitId ? { ...u, status: 'occupied' } : u));
    
    addAuditLog('assign_tenant', `Established 1-year tenancy lease for: ${matchedUser.fullName} on unit ${targetUnit.unitNumber}`);
    alert(`Success: ${matchedUser.fullName} has been successfully assigned to Unit ${targetUnit.unitNumber}!`);
  };

  const handleTerminateTenancy = (tenancyId: number) => {
    const lease = tenancies.find(t => t.id === tenancyId);
    if (!lease) return;

    setTenancies(prev => prev.map(t => t.id === tenancyId ? { ...t, status: 'terminated' } : t));
    setUnits(prev => prev.map(u => u.id === lease.unitId ? { ...u, status: 'vacant' } : u));

    const tenantName = users.find(u => u.id === lease.tenantId)?.fullName;
    addAuditLog('terminate_tenancy', `Terminated lease contract early for tenant: "${tenantName}"`);
  };

  const handleProcessPayment = (id: number, status: 'confirmed' | 'rejected') => {
    const target = payments.find(p => p.id === id);
    if (!target) return;

    setPayments(prev => prev.map(p => p.id === id ? { ...p, status } : p));
    
    const tenantName = users.find(u => u.id === target.tenantId)?.fullName;
    addAuditLog('process_payment', `Updated status of M-Pesa clearing for "${tenantName}" (Month: ${target.monthPaidFor}): ${status.toUpperCase()}`);
    alert(`Rent record processed: ${status.toUpperCase()}`);
  };

  const handleResolveMaintenance = (id: number, notes: string, status: 'in_progress' | 'resolved' | 'closed') => {
    setMaintenance(prev => prev.map(m => m.id === id ? { ...m, status, landlordNotes: notes } : m));
    
    const claim = maintenance.find(m => m.id === id);
    addAuditLog('resolve_maintenance', `Addressed maintenance issue [${claim?.title}]: ${status}`);
  };

  // TENANT OPERATIONS
  const handleTenantSubmitPayment = (amount: number, month: string, method: 'mpesa' | 'bank' | 'cash', code?: string) => {
    if (!currentUser) return;
    const activeLease = tenancies.find(t => t.tenantId === currentUser.id && t.status === 'active');
    if (!activeLease) return;

    const receiptNum = `REC-2026-${Math.floor(Math.random() * 9000 + 1000)}`;
    const newPayment: Payment = {
      id: Date.now(),
      tenancyId: activeLease.id,
      tenantId: currentUser.id,
      landlordId: activeLease.landlordId,
      amount,
      paymentDate: new Date().toISOString().split('T')[0],
      paymentMethod: method,
      mpesaCode: code,
      receiptNumber: receiptNum,
      monthPaidFor: month,
      status: 'pending',
      createdAt: new Date().toISOString()
    };

    setPayments(prev => [newPayment, ...prev]);
    addAuditLog('tenant_submit_payment', `Tenant submitted billing reference ${code || 'CASH'} for KES ${amount}`);
  };

  const handleTenantSubmitMaintenance = (title: string, desc: string, priority: any, category: any) => {
    if (!currentUser) return;
    const activeLease = tenancies.find(t => t.tenantId === currentUser.id && t.status === 'active');
    if (!activeLease) return;

    const newClaim: MaintenanceRequest = {
      id: Date.now(),
      tenancyId: activeLease.id,
      unitId: activeLease.unitId,
      tenantId: currentUser.id,
      landlordId: activeLease.landlordId,
      title,
      description: desc,
      priority,
      category,
      status: 'open',
      photos: [],
      createdAt: new Date().toISOString()
    };

    setMaintenance(prev => [newClaim, ...prev]);
    addAuditLog('tenant_submit_maintenance', `Tenant lodged repair request: "${title}"`);
  };

  const handleMarkNoticeRead = (id: number) => {
    setNotices(prev => prev.map(n => n.id === id ? { ...n, isRead: true } : n));
  };

  // Receipt fetch details
  const getReceiptData = (p: Payment) => {
    const payTenant = users.find(u => u.id === p.tenantId) as User;
    const payLandlord = users.find(u => u.id === p.landlordId) as User;
    const payTenancy = tenancies.find(t => t.id === p.tenancyId);
    const payUnit = payTenancy ? units.find(u => u.id === payTenancy.unitId) : null;
    const payProp = payUnit ? properties.find(pr => pr.id === payUnit.propertyId) : null;

    return {
      payment: p,
      tenant: payTenant || users[2],
      landlord: payLandlord || users[1],
      property: payProp || properties[0],
      unit: payUnit || units[0]
    };
  };

  // Tenant specifics
  const getTenantContext = () => {
    if (!currentUser) return null;
    const lease = tenancies.find(t => t.tenantId === currentUser.id && t.status === 'active') || null;
    const u = lease ? units.find(unit => unit.id === lease.unitId) : null;
    const p = u ? properties.find(prop => prop.id === u.propertyId) : null;
    return { tenancy: lease, unit: u || null, property: p || null };
  };

  return (
    <div className="bg-slate-50 min-h-screen font-sans flex flex-col justify-between selection:bg-brand-gold selection:text-white">
      
      {/* GLOBAL HEADER BAR */}
      <header className="bg-brand-green text-white shadow-md font-display shrink-0">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <div className="flex items-center space-x-2.5">
            <span className="text-xl font-black tracking-tight cursor-pointer" onClick={() => { setCurrentUser(null); setShowLogin(false); }}>
              Keja<span className="text-brand-gold">Connect</span>
            </span>
            <span className="bg-brand-gold/20 text-brand-gold border border-brand-gold/30 rounded-md text-[9px] font-black uppercase tracking-wider px-2 py-0.5">
              Property Hub Sandbox
            </span>
          </div>

          <div className="flex items-center space-x-4">
            {currentUser ? (
              <div className="flex items-center space-x-3.5">
                <div className="text-right">
                  <span className="text-xs font-extrabold block tracking-tight leading-none text-white">{currentUser.fullName}</span>
                  <span className="text-[10px] text-brand-gold uppercase tracking-widest font-bold mt-1 block">Role: {currentUser.role}</span>
                </div>
                <button
                  onClick={handleLogout}
                  className="bg-brand-green-hover hover:bg-neutral-900 border border-white/20 p-2 rounded-xl transition-all cursor-pointer text-white"
                  title="Secure Logout"
                >
                  <LogOut className="h-4 w-4" />
                </button>
              </div>
            ) : (
              <button
                onClick={() => setShowLogin(true)}
                className="bg-brand-gold hover:bg-brand-gold-hover text-white text-xs font-bold py-2.5 px-5 rounded-xl transition-all cursor-pointer shadow-sm"
              >
                Access Portal
              </button>
            )}
          </div>
        </div>
      </header>

      {/* CORE FRAMEWORK MULTIPLEXER */}
      <main className="flex-1 flex flex-col items-center">
        {currentUser ? (
          <div className="w-full max-w-7xl mx-auto">
            {currentUser.role === 'admin' && (
              <AdminDashboard
                users={users}
                properties={properties}
                units={units}
                notices={notices}
                auditLogs={auditLogs}
                onAddUser={handleAddUser}
                onUpdateUserStatus={handleUpdateUserStatus}
                onDeleteUser={handleDeleteUser}
                onSendNotice={handleSendNotice}
              />
            )}
            
            {currentUser.role === 'landlord' && (
              <LandlordDashboard
                properties={properties.filter(p => p.landlordId === currentUser.id)}
                units={units}
                tenancies={tenancies.filter(t => t.landlordId === currentUser.id)}
                payments={payments.filter(p => p.landlordId === currentUser.id)}
                maintenance={maintenance.filter(m => m.landlordId === currentUser.id)}
                tenants={users.filter(u => u.role === 'tenant')}
                onAddProperty={handleAddProperty}
                onDeleteProperty={handleDeleteProperty}
                onAddUnit={handleAddUnit}
                onAssignTenant={handleAssignTenant}
                onTerminateTenancy={handleTerminateTenancy}
                onProcessPayment={handleProcessPayment}
                onResolveMaintenance={handleResolveMaintenance}
                onViewReceipt={setActiveReceipt}
              />
            )}

            {currentUser.role === 'tenant' && (
              <TenantDashboard
                {...getTenantContext()!}
                payments={payments.filter(p => p.tenantId === currentUser.id)}
                maintenance={maintenance.filter(m => m.tenantId === currentUser.id)}
                notices={notices}
                onSubmitPayment={handleTenantSubmitPayment}
                onSubmitMaintenance={handleTenantSubmitMaintenance}
                onViewReceipt={setActiveReceipt}
                onMarkNoticeRead={handleMarkNoticeRead}
              />
            )}
          </div>
        ) : showLogin ? (
          /* LOGIN PANEL OVERLAY */
          <div className="flex-1 w-full max-w-sm flex flex-col justify-center items-center py-12 px-6">
            <div className="bg-white border border-gray-150 p-8 rounded-2xl w-full shadow-lg">
              <div className="text-center mb-6">
                <span className="text-2xl font-black font-display text-brand-green">
                  Keja<span className="text-brand-gold">Connect</span>
                </span>
                <p className="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-semibold">Security Portal Gate</p>
              </div>

              {showForgot ? (
                /* FORGOT FLOW */
                <form onSubmit={(e) => { e.preventDefault(); alert(`Security hash reset link dispatch coordinates queued for ${forgotEmail}`); setForgotEmail(''); setShowForgot(false); }} className="space-y-4 text-xs font-semibold text-slate-650">
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Verify Account Email</label>
                    <input
                      type="email"
                      required
                      placeholder="e.g. wanjiku.tenant@yahoo.com"
                      value={forgotEmail}
                      onChange={(e) => setForgotEmail(e.target.value)}
                      className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden focus:ring-1 focus:ring-brand-green"
                    />
                  </div>
                  <button
                    type="submit"
                    className="w-full bg-brand-green hover:bg-brand-green-hover text-white py-3 rounded-lg font-bold text-xs cursor-pointer shadow-xs"
                  >
                    Send Recovery Email Token
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowForgot(false)}
                    className="text-slate-400 hover:text-slate-700 block text-center w-full pt-1 uppercase tracking-wider text-[9px] font-bold"
                  >
                    Back to authentication
                  </button>
                </form>
              ) : (
                /* REGISTERED CREDENTIALS CHECK */
                <form onSubmit={handleLoginSubmit} className="space-y-4 text-xs font-semibold text-slate-650">
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Login Email Coordinate</label>
                    <div className="relative">
                      <Mail className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                      <input
                        type="email"
                        required
                        placeholder="wanjiku.tenant@yahoo.com"
                        value={loginEmail}
                        onChange={(e) => setLoginEmail(e.target.value)}
                        className="pl-9 w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden focus:ring-1 focus:ring-brand-green font-bold text-slate-800"
                      />
                    </div>
                  </div>
                  <div>
                    <div className="flex justify-between items-center mb-1">
                      <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450">Session Encryption PIN</label>
                      <button type="button" onClick={() => setShowForgot(true)} className="text-[10px] text-brand-gold hover:underline font-bold">Forgot passcode?</button>
                    </div>
                    <div className="relative">
                      <Lock className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                      <input
                        type="password"
                        required
                        placeholder="••••••••"
                        value={loginPassword}
                        onChange={(e) => setLoginPassword(e.target.value)}
                        className="pl-9 w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden focus:ring-1 focus:ring-brand-green"
                      />
                    </div>
                  </div>
                  <button
                    type="submit"
                    className="w-full bg-brand-green hover:bg-brand-green-hover text-white py-3 rounded-lg font-bold text-xs cursor-pointer"
                  >
                    Verify Passcode Security
                  </button>

                  {/* QUICK BYPASS GATEWAY SELECTORS */}
                  <div className="border-t border-slate-100 pt-5 mt-4">
                    <p className="text-[10px] font-bold text-slate-450 text-center uppercase tracking-wider leading-none">Sandbox simulated switchers</p>
                    <div className="grid grid-cols-3 gap-2 mt-3 text-[10px] font-semibold text-center select-none text-slate-650">
                      <button
                        type="button"
                        onClick={() => executeQuickLogin('admin')}
                        className="p-1.5 border border-slate-150 rounded-lg hover:border-brand-gold bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors"
                      >
                        Admin
                      </button>
                      <button
                        type="button"
                        onClick={() => executeQuickLogin('landlord')}
                        className="p-1.5 border border-slate-150 rounded-lg hover:border-brand-green bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors"
                      >
                        Landlord
                      </button>
                      <button
                        type="button"
                        onClick={() => executeQuickLogin('tenant')}
                        className="p-1.5 border border-slate-150 rounded-lg hover:border-brand-gold bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors"
                      >
                        Tenant
                      </button>
                    </div>
                  </div>
                </form>
              )}
            </div>
          </div>
        ) : (
          /* HOMEPAGE VISUAL CARD */
          <LandingHero onEnterPortal={() => setShowLogin(true)} />
        )}
      </main>

      {/* FLOATING ACTION RAILS WRAPPER */}
      <footer className="bg-white border-t border-gray-150 py-6 px-6 shrink-0 z-10">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-slate-450 text-[10px] font-medium text-center md:text-left leading-normal">
            &copy; 2226 KejaConnect Systems. Built with standard strict security constraints (#1a6b3c & #f0a500). Active and responsive.
          </p>

          <button
            onClick={() => setShowSourceCode(!showSourceCode)}
            className="inline-flex items-center space-x-1 border border-slate-205 py-2 px-3.5 rounded-xl text-[10px] font-extrabold uppercase bg-slate-50 hover:bg-slate-100 text-slate-650 cursor-pointer transition-colors"
          >
            <Code className="h-4.5 w-4.5 text-brand-gold shrink-0" />
            <span>{showSourceCode ? 'Hide Model Sources' : 'Inspect Mapped Backend Sources (Vault)'}</span>
          </button>
        </div>

        {/* COMPREHENSIVE BACKEND PHP CODE MODULE */}
        {showSourceCode && (
          <div className="max-w-7xl mx-auto mt-4 px-2">
            <SourceCodeViewer />
          </div>
        )}
      </footer>

      {/* DYNAMIC RECEIPT GENERATOR ENGINES */}
      {activeReceipt && (
        <ReceiptModal
          {...getReceiptData(activeReceipt)}
          onClose={() => setActiveReceipt(null)}
        />
      )}

    </div>
  );
}
