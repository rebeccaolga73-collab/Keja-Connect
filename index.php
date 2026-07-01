<?php
/**
 * KejaConnect - Smarter Real Estate Property Management System in Kenya
 * Landing Homepage & Hero Entrance
 */
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KejaConnect — Smarter Property Management in Kenya</title>
    <!-- Google Fonts Poppins & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9f3',
                            100: '#dcf0e2',
                            600: '#1a6b3c', // primary dark green
                            700: '#155630',
                            accent: '#f0a500' // accent gold
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased text-gray-900 bg-white">

    <!-- NAVIGATION HEADER -->
    <header class="absolute inset-x-0 top-0 z-50">
        <nav class="flex items-center justify-between p-6 lg:px-8 max-w-7xl mx-auto" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5 flex items-center space-x-2">
                    <span class="text-2xl font-extrabold font-brand tracking-tight text-brand-600">Keja<span class="text-brand-accent">Connect</span></span>
                </a>
            </div>
            <div class="flex lg:hidden">
                <a href="<?php echo BASE_URL; ?>/login.php" class="rounded-md bg-brand-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">Login</a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="#features" class="text-sm font-semibold leading-6 text-gray-900 hover:text-brand-600">Platforms Benefits</a>
                <a href="#how-it-works" class="text-sm font-semibold leading-6 text-gray-900 hover:text-brand-600">How It Works</a>
                <a href="#contact" class="text-sm font-semibold leading-6 text-gray-900 hover:text-brand-600">Get Support</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                <a href="<?php echo BASE_URL; ?>/login.php" class="text-sm font-semibold leading-6 text-white bg-brand-600 hover:bg-brand-700 px-5 py-2.5 rounded-lg shadow-sm font-poppins transition-all">Secure Login &rarr;</a>
            </div>
        </nav>
    </header>

    <!-- HERO SECTION -->
    <div class="relative isolate pt-14">
        <!-- Background decorative ambient circles -->
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-1155/678 w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-brand-100 to-brand-accent opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
        </div>

        <div class="py-24 sm:py-32 lg:pb-40">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-600 ring-1 ring-inset ring-brand-600/10 mb-6">Made for modern Kenyan property systems</span>
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl font-brand">
                        KejaConnect — Smarter Property Management in Kenya
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Bridging communication gaps between property admins, landlords, and tenants. Keep track of payments, submit maintenance logs on the go, secure lease archives, and run transparent analytics on a high-speed platform.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="<?php echo BASE_URL; ?>/login.php" class="rounded-md bg-brand-600 px-6 py-3.5 text-base font-bold text-white shadow-md hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition-all font-poppins">Enter Portal Dashboard</a>
                        <a href="#features" class="text-sm font-semibold leading-6 text-gray-900 hover:text-brand-600">Explore Modules <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODULE FEATURES SECTION -->
    <div id="features" class="py-24 bg-gray-50 border-y border-gray-100">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl font-brand">Powering Every Stakeholder Role</h2>
                <p class="mt-4 text-md text-gray-500">Each role is equipped with an isolated workspace built specifically for their properties and action goals.</p>
            </div>

            <div class="mx-auto grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                <!-- FOR ADMINS -->
                <div class="flex flex-col bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 border border-t-4 border-brand-accent">
                    <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 mb-6">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-brand text-gray-900">For System Admins</h3>
                    <p class="mt-4 text-sm leading-6 text-gray-600">
                        Oversee the entire real-estate network database. Moderate registries, flag irregular buildings, review comprehensive total financial payouts, and broadcast warnings.
                    </p>
                    <ul class="mt-6 space-y-3 text-xs text-gray-500 border-t border-gray-100 pt-6 flex-1">
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>Complete User management & audits</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>Aggregate county & landlord collections</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>System security overview</li>
                    </ul>
                </div>

                <!-- FOR LANDLORDS -->
                <div class="flex flex-col bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 border border-t-4 border-brand-600">
                    <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 mb-6">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-brand text-gray-900">For Landlords</h3>
                    <p class="mt-4 text-sm leading-6 text-gray-600">
                        Add and configure property buildings and units. Recruit or link renters, log rent payments manually, approve tenant digital submissions, and track maintenance tickets.
                    </p>
                    <ul class="mt-6 space-y-3 text-xs text-gray-500 border-t border-gray-100 pt-6 flex-1">
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-600 mr-2"></span>Dynamic property & unit creation</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-600 mr-2"></span>Automated M-Pesa validations</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-600 mr-2"></span>Legal lease agreement storage</li>
                    </ul>
                </div>

                <!-- FOR TENANTS -->
                <div class="flex flex-col bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300 border border-t-4 border-brand-accent">
                    <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 mb-6">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-brand text-gray-900">For Tenants</h3>
                    <p class="mt-4 text-sm leading-6 text-gray-600">
                        View current lease dates, download official landlord-assigned contracts, upload digital M-Pesa payment receipts, and lodge maintenance claims directly with your landlord.
                    </p>
                    <ul class="mt-6 space-y-3 text-xs text-gray-500 border-t border-gray-100 pt-6 flex-1">
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>Secure self-service tenant portal</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>Upload payment receipt snapshots</li>
                        <li class="flex items-center"><span class="h-1.5 w-1.5 rounded-full bg-brand-accent mr-2"></span>Maintenance photo submissions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- HOW IT WORKS -->
    <div id="how-it-works" class="py-24 bg-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl font-brand">Smooth Onboarding Operation</h2>
                <p class="mt-4 text-md text-gray-500">Getting your properties and tenancies integrated requires three simple milestones.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="h-12 w-12 rounded-full border-2 border-brand-600 text-brand-600 flex items-center justify-center text-lg font-bold mx-auto mb-4 bg-brand-50">1</div>
                    <h4 class="font-bold text-lg font-brand">Register & Login</h4>
                    <p class="mt-2 text-sm text-gray-500">Admin registers landowners, who then secure landlord logs or invite renters with safe pre-established passwords.</p>
                </div>
                <div class="text-center">
                    <div class="h-12 w-12 rounded-full border-2 border-brand-accent text-brand-accent flex items-center justify-center text-lg font-bold mx-auto mb-4 bg-yellow-50">2</div>
                    <h4 class="font-bold text-lg font-brand">Map Infrastructure</h4>
                    <p class="mt-2 text-sm text-gray-500">Landlords create properties, map individual units (numbers, rent structures, deposits) and trigger formal contract leases.</p>
                </div>
                <div class="text-center">
                    <div class="h-12 w-12 rounded-full border-2 border-brand-600 text-brand-600 flex items-center justify-center text-lg font-bold mx-auto mb-4 bg-brand-50">3</div>
                    <h4 class="font-bold text-lg font-brand">Collect & Track</h4>
                    <p class="mt-2 text-sm text-gray-500">Renters submit payments using regular M-Pesa. Payments are reviewed, receipts generated instantly, and audits preserved clean.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER WITH CONTACT INFO -->
    <footer id="contact" class="bg-gray-900 text-gray-400 py-12 border-t border-gray-800">
        <div class="mx-auto max-w-7xl px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <span class="text-2xl font-extrabold font-brand tracking-tight text-brand-accent">Keja<span class="text-white">Connect</span></span>
                <p class="mt-4 text-sm text-gray-400 leading-6">Smarter and faster property ecosystem for landlords, managers, and tenants inside Kenya.</p>
            </div>
            <div>
                <h5 class="text-white font-semibold text-sm tracking-wider uppercase">Support Contacts</h5>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>Email: <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="hover:text-white"><?php echo SUPPORT_EMAIL; ?></a></li>
                    <li>Phone: +254 712 345 678</li>
                    <li>Offices: Westlands Commercial Center, Nairobi</li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-semibold text-sm tracking-wider uppercase">System Gateways</h5>
                <div class="mt-4 flex flex-col space-y-2 text-sm">
                    <a href="<?php echo BASE_URL; ?>/login.php" class="text-brand-accent hover:text-white">&rarr; Proceed to Login screen</a>
                    <p class="text-xs text-gray-500">For security assistance, password resets, or onboarding questions, please contact your respective property manager.</p>
                </div>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-6 lg:px-8 border-t border-gray-800 mt-12 pt-6 text-center text-xs">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_COMPANY; ?>. All rights reserved. Built with PHP Core standard protection.</p>
        </div>
    </footer>

</body>
</html>
