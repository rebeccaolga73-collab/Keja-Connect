import React from 'react';
import { Home, Shield, Users, ArrowRight, Settings } from 'lucide-react';

interface LandingHeroProps {
  onEnterPortal: () => void;
}

export default function LandingHero({ onEnterPortal }: LandingHeroProps) {
  return (
    <div className="bg-white min-h-screen">
      {/* Navigation */}
      <nav className="max-w-7xl mx-auto px-6 lg:px-8 py-5 flex items-center justify-between border-b border-gray-150">
        <div className="flex items-center space-x-2">
          <span className="text-2xl font-black font-display tracking-tight text-brand-green">
            Keja<span className="text-brand-gold">Connect</span>
          </span>
        </div>
        <div className="flex gap-4">
          <button
            onClick={onEnterPortal}
            className="bg-brand-green hover:bg-brand-green-hover text-white text-xs font-bold font-display px-5 py-2.5 rounded-xl cursor-pointer shadow-sm transition-all"
          >
            Enter Dashboard Portal &rarr;
          </button>
        </div>
      </nav>

      {/* Hero Section */}
      <div className="relative isolate overflow-hidden pt-12 pb-20 sm:pb-32">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="mx-auto max-w-3xl text-center">
            <span className="inline-flex items-center rounded-full bg-green-50 px-3.5 py-1 text-xs font-semibold text-brand-green ring-1 ring-inset ring-brand-green/20 mb-6">
              Official Kenyan Property Solution Workspace
            </span>
            <h1 className="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl font-display leading-[1.15]">
              KejaConnect — Smarter Property Management in Kenya
            </h1>
            <p className="mt-6 text-sm sm:text-base leading-relaxed text-slate-500 max-w-2xl mx-auto">
              Bridging communications between system administrators, landlords, and busy tenants. Track rental accounts, submit instant M-Pesa receipts, and file structural repairs instantly.
            </p>
            <div className="mt-10 flex items-center justify-center gap-4">
              <button
                onClick={onEnterPortal}
                className="rounded-xl bg-brand-green hover:bg-brand-green-hover text-white px-6 py-3.5 text-sm font-bold shadow-md cursor-pointer transition-colors font-display"
              >
                Access Portal Gateways
              </button>
              <a
                href="#features"
                className="text-xs font-bold text-slate-650 hover:text-brand-green flex items-center space-x-1"
              >
                <span>Learn how it works</span>
                <ArrowRight className="h-4 w-4" />
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* Role Benefits */}
      <div id="features" className="py-20 bg-slate-50 border-t border-slate-100">
        <div className="max-w-7xl mx-auto px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl font-extrabold font-display text-slate-900">Dedicated Role Workspaces</h2>
            <p className="text-slate-400 text-xs mt-2 font-medium">Isolated, custom modules for each stakeholder in the property chain</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {/* Admin card */}
            <div className="bg-white rounded-2xl p-8 border border-slate-150 border-t-4 border-brand-gold shadow-sm hover:shadow-md transition-shadow">
              <div className="h-10 w-10 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center mb-6">
                <Settings className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold font-display text-slate-900">For Administrators</h3>
              <p className="mt-3 text-xs leading-relaxed text-slate-500">
                Register landowners and renters safely. View system audits, track cross-county performance grids, and issue general notice updates.
              </p>
            </div>

            {/* Landlord card */}
            <div className="bg-white rounded-2xl p-8 border border-slate-150 border-t-4 border-brand-green shadow-sm hover:shadow-md transition-shadow">
              <div className="h-10 w-10 rounded-xl bg-brand-green/10 text-brand-green flex items-center justify-center mb-6">
                <Home className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold font-display text-slate-900">For Landlords</h3>
              <p className="mt-3 text-xs leading-relaxed text-slate-500">
                Register property blocks and individual unit rooms. Review submitted tenant transaction reference codes and manage plumbing reports.
              </p>
            </div>

            {/* Tenant card */}
            <div className="bg-white rounded-2xl p-8 border border-slate-150 border-t-4 border-brand-gold shadow-sm hover:shadow-md transition-shadow">
              <div className="h-10 w-10 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center mb-6">
                <Users className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold font-display text-slate-900">For Tenants</h3>
              <p className="mt-3 text-xs leading-relaxed text-slate-500">
                Inspect lease details, submit M-Pesa transaction codes to verify monthly cleared rent, and log maintenance tickets with screenshots.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
