import React from 'react';
import { X, CheckCircle, Printer, Download } from 'lucide-react';
import { Payment, Property, Unit, Tenancy, User } from '../types';

interface ReceiptModalProps {
  payment: Payment;
  tenant: User;
  landlord: User;
  property: Property;
  unit: Unit;
  onClose: () => void;
}

export default function ReceiptModal({
  payment,
  tenant,
  landlord,
  property,
  unit,
  onClose
}: ReceiptModalProps) {
  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 overflow-y-auto backdrop-blur-xs">
      <div id="printable-receipt" className="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col my-8">
        {/* MODAL CONTROL HEADER - Hidden when printing */}
        <div className="bg-slate-50 px-6 py-4 border-b border-gray-150 flex items-center justify-between print:hidden">
          <div className="flex items-center space-x-2">
            <CheckCircle className="h-5 w-5 text-brand-green" />
            <span className="font-display font-bold text-slate-850 text-sm">Rent Receipt Generator</span>
          </div>
          <div className="flex items-center space-x-2">
            <button
              onClick={handlePrint}
              className="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg cursor-pointer transition-colors"
              title="Print Receipt"
            >
              <Printer className="h-4 w-4" />
            </button>
            <button
              onClick={onClose}
              className="p-2 text-slate-400 hover:text-slate-650 hover:bg-slate-100 rounded-lg cursor-pointer transition-colors"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        </div>

        {/* PRINTABLE RECEIPT CONTENT CONTAINER */}
        <div className="p-8 md:p-12 text-slate-800 flex flex-col self-stretch">
          
          {/* TOP BRAND BAR */}
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-slate-100 pb-8 gap-4">
            <div>
              <span className="text-3xl font-black font-display text-brand-green">
                Keja<span className="text-brand-gold">Connect</span>
              </span>
              <p className="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">Smarter Property Management in Kenya</p>
            </div>
            <div className="text-right sm:text-right text-xs">
              <span className="bg-brand-green/10 text-brand-green border border-brand-green/20 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider">
                Official Receipt
              </span>
              <p className="text-slate-400 mt-3 font-semibold">Receipt No: <span className="text-black font-mono font-bold">{payment.receiptNumber}</span></p>
              <p className="text-slate-450 mt-1">Date: {new Date(payment.paymentDate).toLocaleDateString('en-KE', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
            </div>
          </div>

          {/* SENDER & RECEIVER GRIDS */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-100 text-xs">
            <div>
              <p className="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Received From (Tenant)</p>
              <p className="font-bold text-slate-900 text-sm mt-1">{tenant.fullName}</p>
              <p className="text-slate-500 mt-1">Email: {tenant.email}</p>
              <p className="text-slate-500">Phone: {tenant.phone}</p>
              <p className="text-slate-500 mt-2 font-medium">Mapped Residenc: <span className="font-bold text-slate-705">Unit {unit.unitNumber}</span>, Floor {unit.floor}</p>
              <p className="text-slate-500 font-semibold">{property.name}, {property.address}, {property.county}</p>
            </div>
            <div>
              <p className="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Issued By (Landlord/Manager)</p>
              <p className="font-bold text-slate-900 text-sm mt-1">{landlord.fullName}</p>
              <p className="text-slate-500 mt-1">Email: {landlord.email}</p>
              <p className="text-slate-500">Phone: {landlord.phone}</p>
              <p className="text-slate-450 mt-4 leading-normal italic">
                Thank you for choosing Greenwood housing solutions. Payments must settle by index 5th.
              </p>
            </div>
          </div>

          {/* FINANCIAL SUMMARY TABLE */}
          <div className="py-8">
            <h4 className="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-4">Payment Summary</h4>
            <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-150">
              <div className="grid grid-cols-3 bg-slate-150/50 p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                <div>Description</div>
                <div className="text-center">Reference M-Pesa Code</div>
                <div className="text-right">Line Total</div>
              </div>
              <div className="grid grid-cols-3 p-4 text-xs font-semibold text-slate-800 border-b border-slate-100">
                <div>Rent Payment for Month of {new Date(payment.monthPaidFor + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</div>
                <div className="text-center font-mono font-bold text-brand-green">{payment.mpesaCode || 'CASH/BANK'}</div>
                <div className="text-right font-bold text-slate-900">KES {payment.amount.toLocaleString()}</div>
              </div>
              <div className="grid grid-cols-3 bg-slate-100/30 p-4 text-xs font-bold text-slate-900">
                <div className="col-span-2 text-right">Grand Total Paid</div>
                <div className="text-right text-brand-green text-sm font-black">KES {payment.amount.toLocaleString()}</div>
              </div>
            </div>
          </div>

          {/* BOTTOM VERIFICATION AND SECURITY STAMP */}
          <div className="flex flex-col sm:flex-row justify-between items-center bg-green-50/40 rounded-xl p-5 border border-brand-green/10 mt-2 gap-4">
            <div className="text-xs text-brand-green">
              <p className="font-bold uppercase tracking-wider flex items-center shrink-0">
                <span>Verification State: Secured</span>
              </p>
              <p className="text-[10px] text-slate-500 mt-0.5 leading-normal">This record constitutes an official legal receipt showing transactional release matching standard banking allocations.</p>
            </div>
            
            {/* Signature Block */}
            <div className="border-t border-slate-200 pt-3 text-center w-40 shrink-0">
              <span className="text-[10px] font-display font-bold text-brand-green block">Mwenda Joseph</span>
              <p className="text-[9px] text-slate-400 uppercase mt-0.5 font-semibold">Authorized Signature</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
