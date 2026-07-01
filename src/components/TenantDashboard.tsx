import React, { useState } from 'react';
import { Tenancy, Payment, MaintenanceRequest, Notice, Property, Unit } from '../types';
import { Home, Key, FileText, Send, CheckCircle, HelpCircle, Eye } from 'lucide-react';

interface TenantDashboardProps {
  tenancy: Tenancy | null;
  property: Property | null;
  unit: Unit | null;
  payments: Payment[];
  maintenance: MaintenanceRequest[];
  notices: Notice[];
  onSubmitPayment: (amount: number, month: string, method: 'mpesa' | 'bank' | 'cash', code?: string) => void;
  onSubmitMaintenance: (title: string, desc: string, priority: 'low' | 'medium' | 'high' | 'urgent', category: 'plumbing' | 'electrical' | 'structural' | 'cleaning' | 'other') => void;
  onViewReceipt: (payment: Payment) => void;
  onMarkNoticeRead: (id: number) => void;
}

export default function TenantDashboard({
  tenancy,
  property,
  unit,
  payments,
  maintenance,
  notices,
  onSubmitPayment,
  onSubmitMaintenance,
  onViewReceipt,
  onMarkNoticeRead
}: TenantDashboardProps) {
  const [activeTab, setActiveTab] = useState<'dashboard' | 'payments' | 'maintenance' | 'notices'>('dashboard');

  // Submit payment form state
  const [payMonth, setPayMonth] = useState('2026-06');
  const [payAmount, setPayAmount] = useState(25000);
  const [payMethod, setPayMethod] = useState<'mpesa' | 'bank' | 'cash'>('mpesa');
  const [payMpesaCode, setPayMpesaCode] = useState('');

  // Submit maintenance state
  const [maintTitle, setMaintTitle] = useState('');
  const [maintDesc, setMaintDesc] = useState('');
  const [maintPriority, setMaintPriority] = useState<'low' | 'medium' | 'high' | 'urgent'>('medium');
  const [maintCategory, setMaintCategory] = useState<'plumbing' | 'electrical' | 'structural' | 'cleaning' | 'other'>('plumbing');

  const handlePaymentSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!payAmount || (payMethod === 'mpesa' && !payMpesaCode)) return;
    onSubmitPayment(Number(payAmount), payMonth, payMethod, payMpesaCode || undefined);
    setPayMpesaCode('');
    alert('Rental clearance receipt submitted. Landlord will review shortly!');
  };

  const handleMaintenanceSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!maintTitle || !maintDesc) return;
    onSubmitMaintenance(maintTitle, maintDesc, maintPriority, maintCategory);
    setMaintTitle('');
    setMaintDesc('');
    alert('Maintenance ticket opened. Landlord has been alerted!');
  };

  if (!tenancy || !property || !unit) {
    return (
      <div className="bg-white p-8 rounded-2xl border border-gray-150 max-w-2xl mx-auto my-12 text-center text-slate-700">
        <div className="h-12 w-12 bg-yellow-50 text-brand-gold rounded-full flex items-center justify-center mx-auto mb-4">
          <HelpCircle className="h-6 w-6" />
        </div>
        <h3 className="text-lg font-bold font-display text-slate-900 mb-2">Awaiting Lease Placement</h3>
        <p className="text-xs leading-relaxed text-slate-500">
          Your tenant profile is active, but your property manager has not yet mapped an active lease to your account.
          Please coordinate with your Landlord to establish an active tenancy agreement.
        </p>
      </div>
    );
  }

  // Calculated next due:
  const lastConfirmedPayment = payments.find((p) => p.status === 'confirmed');
  const lastPendingPayment = payments.find((p) => p.status === 'pending');

  return (
    <div className="flex flex-col self-stretch bg-slate-50 min-h-screen text-slate-700">
      {/* Tab Navigation header */}
      <div className="bg-white border-b border-gray-150 py-3.5 px-6 flex flex-row items-center space-x-6 overflow-x-auto shrink-0">
        <button
          onClick={() => setActiveTab('dashboard')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'dashboard' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Home className="h-4 w-4" />
          <span>My Living Space Summary</span>
        </button>
        <button
          onClick={() => setActiveTab('payments')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'payments' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <FileText className="h-4 w-4" />
          <span>Settle Rental Payments ({payments.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('maintenance')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'maintenance' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <Send className="h-4 w-4 text-brand-gold" />
          <span>Lodge Maintenance Tickets ({maintenance.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('notices')}
          className={`text-xs font-bold font-display cursor-pointer transition-colors pb-1 border-b-2 flex items-center space-x-1.5 ${
            activeTab === 'notices' ? 'text-brand-green border-brand-green' : 'text-slate-400 border-transparent hover:text-slate-800'
          }`}
        >
          <FileText className="h-4 w-4" />
          <span>Announcements ({notices.length})</span>
        </button>
      </div>

      <div className="p-6 overflow-y-auto flex-1">
        
        {/* DASHBOARD TAB */}
        {activeTab === 'dashboard' && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {/* Left Columns - Unit Details */}
            <div className="lg:col-span-2 space-y-6">
              
              {/* Unit Info Card */}
              <div className="bg-white rounded-xl border border-gray-150 p-6 shadow-xs flex flex-col justify-between">
                <div>
                  <span className="bg-brand-green/10 text-brand-green text-[9px] font-black px-2.5 py-1 rounded-full uppercase border border-brand-green/20">Active Lease Contract</span>
                  <h3 className="font-display font-black text-slate-900 text-lg mt-4">{property.name}</h3>
                  <p className="text-xs text-slate-400 font-medium mt-0.5">{property.address}, <span className="text-brand-green font-bold">{property.county}</span></p>
                  
                  <div className="mt-6 grid grid-cols-3 gap-4 border-t border-slate-100 pt-5 text-center text-xs">
                    <div>
                      <span className="text-slate-400 block font-bold text-[9px] uppercase tracking-wider">Unit Number</span>
                      <span className="font-mono text-base font-bold text-slate-850 mt-1 block">Unit {unit.unitNumber}</span>
                    </div>
                    <div>
                      <span className="text-slate-400 block font-bold text-[9px] uppercase tracking-wider">Floor Level</span>
                      <span className="font-mono text-base font-bold text-slate-850 mt-1 block">Floor {unit.floor}</span>
                    </div>
                    <div>
                      <span className="text-slate-400 block font-bold text-[9px] uppercase tracking-wider">Monthly Rent</span>
                      <span className="text-brand-green text-base font-black mt-1 block">KES {unit.rentAmount.toLocaleString()}</span>
                    </div>
                  </div>
                </div>

                <div className="border-t border-slate-100 mt-6 pt-4 text-xs font-medium text-slate-500">
                  <div className="flex justify-between">
                    <span>Lease Term Boundary:</span>
                    <span>{tenancy.startDate} to {tenancy.endDate}</span>
                  </div>
                </div>
              </div>

              {/* Fast Payments summary */}
              <div className="bg-white rounded-xl border border-gray-150 p-6">
                <h4 className="font-display font-bold text-slate-900 text-sm mb-4">Last Payment Status Clearance</h4>
                {lastConfirmedPayment ? (
                  <div className="p-4 bg-green-50 border border-green-150 rounded-xl flex justify-between items-center text-xs">
                    <div>
                      <span className="font-bold text-green-800">Settle for {lastConfirmedPayment.monthPaidFor} — Confirmed</span>
                      <p className="text-green-600 mt-0.5 font-semibold">Receipt ID: {lastConfirmedPayment.receiptNumber} | Amount: KES {lastConfirmedPayment.amount.toLocaleString()}</p>
                    </div>
                    <button
                      onClick={() => onViewReceipt(lastConfirmedPayment)}
                      className="bg-brand-green hover:bg-brand-green-hover text-white px-3.5 py-1.5 rounded-md font-bold text-[10px] cursor-pointer"
                    >
                      View Slip Receipt
                    </button>
                  </div>
                ) : lastPendingPayment ? (
                  <div className="p-4 bg-yellow-50 border border-yellow-150 rounded-xl flex justify-between items-center text-xs">
                    <div>
                      <span className="font-bold text-yellow-805">Settle for {lastPendingPayment.monthPaidFor} — Processing</span>
                      <p className="text-yellow-600 mt-0.5 font-semibold">Ref M-Pesa Code: {lastPendingPayment.mpesaCode} | Amount KES {lastPendingPayment.amount.toLocaleString()}</p>
                    </div>
                    <span className="text-[10px] text-slate-400 italic">Reviewing...</span>
                  </div>
                ) : (
                  <p className="text-xs text-slate-400 font-medium">No system receipts recorded so far.</p>
                )}
              </div>

            </div>

            {/* Right side - Notices/Landlord contact */}
            <div className="space-y-6">
              <div className="bg-white rounded-xl border border-gray-150 p-6 flex flex-col justify-between">
                <div>
                  <h4 className="font-display font-bold text-slate-900 text-sm mb-4">Direct Manager Contacts</h4>
                  <div className="p-4 bg-slate-50 border border-slate-150 rounded-lg text-xs space-y-2.5">
                    <p className="font-bold text-slate-800">Mwenda Joseph (Landlord)</p>
                    <p className="text-slate-500">Email: mwenda.landlord@gmail.com</p>
                    <p className="text-slate-500">Hotline Mobile: +254722112233</p>
                  </div>
                </div>
                
                <div className="border-t border-slate-100 mt-6 pt-4 text-[10px] text-slate-400 italic font-semibold">
                  For lock replacements or immediate security distress, contact Nairobi county emergency services or your landlord directly.
                </div>
              </div>
            </div>

          </div>
        )}

        {/* PAYMENTS TAB */}
        {activeTab === 'payments' && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Submit Reference Form */}
            <div className="bg-white p-6 rounded-xl border border-gray-150">
              <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Submit M-Pesa Rent Record</h3>
              <form onSubmit={handlePaymentSubmit} className="space-y-4 text-xs text-slate-600 font-semibold">
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Target Billing Month</label>
                  <input
                    type="month"
                    value={payMonth}
                    onChange={(e) => setPayMonth(e.target.value)}
                    className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Paid Amount (KES)</label>
                  <input
                    type="number"
                    value={payAmount}
                    onChange={(e) => setPayAmount(Number(e.target.value))}
                    className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Transaction Route ID</label>
                  <select
                    value={payMethod}
                    onChange={(e) => setPayMethod(e.target.value as any)}
                    className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  >
                    <option value="mpesa">Safaricom Mobile M-Pesa Code</option>
                    <option value="bank">Direct Equity / KCB Bank Slip</option>
                    <option value="cash">Direct Cash Handover</option>
                  </select>
                </div>
                {payMethod === 'mpesa' && (
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">M-Pesa Reference Code</label>
                    <span className="text-[10px] text-slate-400 block mb-1">Enter 10 character code (e.g. QRF4YHN8ML)</span>
                    <input
                      type="text"
                      required
                      placeholder="QRF4YHN8ML"
                      value={payMpesaCode}
                      onChange={(e) => setPayMpesaCode(e.target.value.toUpperCase())}
                      className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden font-mono"
                    />
                  </div>
                )}
                <button
                  type="submit"
                  className="w-full bg-brand-green hover:bg-brand-green-hover text-white py-3.5 rounded-lg font-bold text-xs cursor-pointer shadow-xs transition-colors"
                >
                  Submit Payment Slip
                </button>
              </form>
            </div>

            {/* History Table */}
            <div className="bg-white p-6 rounded-xl border border-gray-150 lg:col-span-2 overflow-hidden">
              <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Historical Receipts Archive</h3>
              <div className="overflow-x-auto select-none">
                <table className="min-w-full divide-y divide-gray-200 text-xs">
                  <thead className="bg-slate-50 text-slate-500">
                    <tr>
                      <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Stamp</th>
                      <th className="px-6 py-3 text-left font-bold uppercase tracking-wider">Billing Period</th>
                      <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">M-Pesa Code</th>
                      <th className="px-6 py-3 text-right font-bold uppercase tracking-wider">Amount</th>
                      <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Status</th>
                      <th className="px-6 py-3 text-center font-bold uppercase tracking-wider">Slip</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-150 bg-white">
                    {payments.map((p) => (
                      <tr key={p.id} className="hover:bg-slate-50/20">
                        <td className="px-6 py-3.5 whitespace-nowrap text-slate-400">{p.paymentDate}</td>
                        <td className="px-6 py-3.5 whitespace-nowrap font-bold text-slate-800">{p.monthPaidFor}</td>
                        <td className="px-6 py-3.5 whitespace-nowrap text-center font-mono font-bold text-brand-green">{p.mpesaCode || 'CASH/BANK'}</td>
                        <td className="px-6 py-3.5 whitespace-nowrap text-right font-black text-slate-900">KES {p.amount.toLocaleString()}</td>
                        <td className="px-6 py-3.5 whitespace-nowrap text-center">
                          <span className={`text-[9px] font-bold px-2 py-0.5 rounded-full border ${
                            p.status === 'confirmed' ? 'bg-green-105 text-green-800 border-green-200' : p.status === 'rejected' ? 'bg-red-101 text-red-800 border-red-200' : 'bg-yellow-105 text-yellow-805 border-yellow-200'
                          }`}>
                            {p.status}
                          </span>
                        </td>
                        <td className="px-6 py-3.5 whitespace-nowrap text-center">
                          {p.status === 'confirmed' ? (
                            <button
                              onClick={() => onViewReceipt(p)}
                              className="text-brand-green hover:underline font-bold text-[10px]"
                            >
                              Show PDF Receipt
                            </button>
                          ) : (
                            <span className="text-slate-400 font-semibold text-[10px]">Awaiting check</span>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* MAINTENANCE LOGS TAB */}
        {activeTab === 'maintenance' && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 text-xs text-slate-700">
            {/* Lodge New issue */}
            <div className="bg-white p-6 rounded-xl border border-gray-150">
              <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Lodge Maintenance Claim</h3>
              <form onSubmit={handleMaintenanceSubmit} className="space-y-4 font-semibold text-slate-600">
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Issue Topic/Headline</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Dripping bathroom tap"
                    value={maintTitle}
                    onChange={(e) => setMaintTitle(e.target.value)}
                    className="w-full py-2.5 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                  />
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Category Type</label>
                    <select
                      value={maintCategory}
                      onChange={(e) => setMaintCategory(e.target.value as any)}
                      className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                    >
                      <option value="plumbing">Plumbing Works</option>
                      <option value="electrical">Electrical Short</option>
                      <option value="structural">Wall or Door Lock</option>
                      <option value="cleaning">Garbage / Dust</option>
                      <option value="other">Other general</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Urgency Priority</label>
                    <select
                      value={maintPriority}
                      onChange={(e) => setMaintPriority(e.target.value as any)}
                      className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden"
                    >
                      <option value="low">Low concern</option>
                      <option value="medium">Medium concern</option>
                      <option value="high">High concern</option>
                      <option value="urgent">Urgent distress</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label className="block text-[10px] font-bold uppercase tracking-widest text-slate-450 mb-1">Elaborate Damage Details</label>
                  <textarea
                    required
                    rows={4}
                    placeholder="Describe how leaks occur..."
                    value={maintDesc}
                    onChange={(e) => setMaintDesc(e.target.value)}
                    className="w-full py-2 px-3 border border-slate-200 rounded-lg bg-slate-50 focus:outline-hidden focus:ring-1 focus:ring-brand-green"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  className="w-full bg-brand-green hover:bg-brand-green-hover text-white py-3 rounded-lg font-bold text-xs"
                >
                  Submit Repair Request
                </button>
              </form>
            </div>

            {/* My Active requests */}
            <div className="bg-white p-6 rounded-xl border border-gray-150 lg:col-span-2 overflow-hidden">
              <h3 className="font-display font-bold text-slate-900 text-sm mb-4">My Repair logs Status</h3>
              <div className="space-y-4 max-h-[380px] overflow-y-auto">
                {maintenance.map((m) => (
                  <div key={m.id} className="p-4 border border-slate-150 rounded-xl bg-slate-50/50">
                    <div className="flex justify-between items-start gap-2">
                      <span className="bg-slate-200 font-bold uppercase text-[9px] px-2 py-0.5 rounded-full z-10">{m.category} | {m.priority}</span>
                      <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                        m.status === 'resolved' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-101 text-yellow-805 border-yellow-250'
                      }`}>{m.status}</span>
                    </div>

                    <h4 className="font-bold text-slate-905 mt-3 text-sm">{m.title}</h4>
                    <p className="text-slate-500 mt-1 leading-normal">{m.description}</p>
                    
                    {m.landlordNotes && (
                      <div className="mt-3 p-3 bg-brand-green/5 border border-brand-green/10 rounded-lg text-brand-green">
                        <p className="text-[10px] font-bold uppercase">Landlord Feedback</p>
                        <p className="mt-0.5 font-bold">{m.landlordNotes}</p>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* NOTICES LIST TAB */}
        {activeTab === 'notices' && (
          <div className="bg-white rounded-xl border border-gray-150 p-6">
            <h3 className="font-display font-bold text-slate-900 text-sm mb-4">Urgent Announcements from Admin & Managers</h3>
            <div className="space-y-4">
              {notices.map((n) => (
                <div key={n.id} className={`p-4 rounded-xl border flex flex-col sm:flex-row justify-between sm:items-center gap-4 ${
                  n.isRead ? 'bg-slate-50 border-slate-100 text-slate-500' : 'bg-green-50/30 border-brand-green/10 text-slate-800'
                }`}>
                  <div>
                    <h4 className="font-bold text-xs">{n.subject}</h4>
                    <p className="text-xs leading-normal mt-1 text-slate-500">{n.message}</p>
                    <span className="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-brand-gold">{n.type}</span>
                  </div>

                  {!n.isRead && (
                    <button
                      onClick={() => onMarkNoticeRead(n.id)}
                      className="bg-brand-green hover:bg-brand-green-hover text-white text-[10px] font-bold px-3.5 py-1.5 rounded-md cursor-pointer shrink-0"
                    >
                      Acknowledge/Mark Read
                    </button>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

      </div>
    </div>
  );
}
