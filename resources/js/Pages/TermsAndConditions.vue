<script setup>
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
</script>

<template>
    <Head title="Web Hosting Terms & Conditions" />

    <LandingLayout>
        <section class="w-full py-5 bg-light border-top border-bottom">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 p-lg-5">
                                <p class="text-primary text-uppercase fs-12 fw-semibold mb-2">Legal</p>
                                <h1 class="fw-bold mb-3">Web Hosting Terms &amp; Conditions</h1>
                                <p class="text-muted mb-4">
                                    These terms govern all hosting services provided through our managed VPS infrastructure.
                                    By using the service, the client agrees to the following conditions.
                                </p>

                                <h5 class="fw-semibold mt-4">1. Service Overview</h5>
                                <p class="text-muted">
                                    We provide web hosting services using a Virtual Private Server (VPS) environment.
                                    All hosting services are managed and maintained by us, including server setup, security, and basic maintenance.
                                </p>

                                <h5 class="fw-semibold mt-4">2. Resource Allocation (No Unlimited Usage)</h5>
                                <p class="text-muted mb-2">
                                    This is a shared VPS environment, not unlimited hosting. Each client is allocated a fair share of server resources including:
                                </p>
                                <ul class="text-muted">
                                    <li>CPU usage</li>
                                    <li>RAM</li>
                                    <li>Disk space</li>
                                    <li>Bandwidth</li>
                                </ul>
                                <p class="text-muted">
                                    Excessive usage that impacts server performance may result in temporary suspension, request to upgrade plan, or service limitation.
                                </p>

                                <h5 class="fw-semibold mt-4">3. Server Management</h5>
                                <p class="text-muted mb-2">
                                    The server is fully managed by us. Clients do not get root or full server access. We handle:
                                </p>
                                <ul class="text-muted">
                                    <li>Server configuration</li>
                                    <li>Security updates</li>
                                    <li>Monitoring</li>
                                </ul>
                                <p class="text-muted">Any custom server-level changes must be requested and approved.</p>

                                <h5 class="fw-semibold mt-4">4. Acceptable Usage Policy</h5>
                                <p class="text-muted mb-2">Clients must not:</p>
                                <ul class="text-muted">
                                    <li>Host illegal content</li>
                                    <li>Run resource-heavy scripts that degrade performance</li>
                                    <li>Use hosting for spam, phishing, or malicious activities</li>
                                    <li>Upload malware or harmful software</li>
                                </ul>
                                <p class="text-muted">Violation may lead to immediate suspension without notice.</p>

                                <h5 class="fw-semibold mt-4">5. Backups</h5>
                                <p class="text-muted">
                                    We may take periodic backups, but backup is not guaranteed. Clients are responsible for maintaining their own backups.
                                    We are not liable for data loss.
                                </p>

                                <h5 class="fw-semibold mt-4">6. Uptime &amp; Maintenance</h5>
                                <p class="text-muted">
                                    We aim for high uptime but do not guarantee 100% uptime. Scheduled maintenance or unexpected downtime may occur.
                                    No compensation is provided for downtime.
                                </p>

                                <h5 class="fw-semibold mt-4">7. Payment Terms</h5>
                                <p class="text-muted">
                                    Hosting is provided on a paid basis (monthly/yearly). Failure to pay on time may result in suspension
                                    and permanent deletion after a grace period. No refunds once the service is activated.
                                </p>

                                <h5 class="fw-semibold mt-4">8. Security</h5>
                                <p class="text-muted mb-2">Basic server-level security is managed by us. Clients are responsible for:</p>
                                <ul class="text-muted">
                                    <li>Application-level security</li>
                                    <li>Strong passwords</li>
                                    <li>Keeping their website code secure</li>
                                </ul>

                                <h5 class="fw-semibold mt-4">9. Support Scope</h5>
                                <p class="text-muted mb-2">We provide support for:</p>
                                <ul class="text-muted">
                                    <li>Hosting-related issues</li>
                                    <li>Server downtime</li>
                                    <li>Basic deployment help</li>
                                </ul>
                                <p class="text-muted mb-2">We do not provide:</p>
                                <ul class="text-muted">
                                    <li>Full application development support</li>
                                    <li>Debugging client code (unless agreed separately)</li>
                                </ul>

                                <h5 class="fw-semibold mt-4">10. Termination</h5>
                                <p class="text-muted">
                                    We reserve the right to suspend or terminate services if terms are violated, resource usage is excessive,
                                    or payment is not received.
                                </p>

                                <h5 class="fw-semibold mt-4">11. Limitation of Liability</h5>
                                <p class="text-muted mb-2">We are not responsible for:</p>
                                <ul class="text-muted">
                                    <li>Data loss</li>
                                    <li>Business loss due to downtime</li>
                                    <li>Security breaches caused by client-side issues</li>
                                </ul>

                                <h5 class="fw-semibold mt-4">12. Changes to Terms</h5>
                                <p class="text-muted mb-0">
                                    These terms may be updated at any time. Continued use of the service means acceptance of updated terms.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
