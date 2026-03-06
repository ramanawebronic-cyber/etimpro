import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
    title: "Privacy Policy | ETIM Pro",
    description: "Privacy Policy for the ETIM Pro WooCommerce plugin and related services.",
};

export default function PrivacyPage() {
    return (
        <div className="pt-24 pb-16">
            {/* Header */}
            <div className="bg-gradient-to-b from-slate-50 to-white border-b border-slate-100">
                <div className="container mx-auto max-w-4xl px-4 py-16 text-center">
                    <h1 className="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 mb-4">
                        Privacy Policy
                    </h1>
                    <p className="text-slate-500 text-lg">
                        Effective Date: January 1, 2025 &middot; Last Updated: March 1, 2026
                    </p>
                </div>
            </div>

            {/* Content */}
            <div className="container mx-auto max-w-4xl px-4 py-12">
                <div className="prose prose-slate prose-lg max-w-none">
                    {/* Introduction */}
                    <p className="text-slate-600 leading-relaxed text-lg mb-8">
                        At Things at Web Sweden AB (&ldquo;ETIM Pro&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;, or &ldquo;our&rdquo;), we are committed to protecting your privacy and ensuring transparency about how we handle your personal data. This Privacy Policy explains what information we collect, how we use it, and what rights you have regarding your data.
                    </p>

                    {/* Quick Nav */}
                    <div className="bg-slate-50 rounded-2xl p-6 mb-12 not-prose">
                        <h3 className="font-semibold text-slate-800 mb-3 text-sm uppercase tracking-wider">Quick Navigation</h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            {[
                                { label: "1. Information We Collect", id: "collect" },
                                { label: "2. How We Use Your Information", id: "use" },
                                { label: "3. Data Storage & Security", id: "storage" },
                                { label: "4. Data Sharing & Disclosure", id: "sharing" },
                                { label: "5. Your Data Protection Rights", id: "rights" },
                                { label: "6. Cookies & Tracking", id: "cookies" },
                                { label: "7. Compliance & International Transfers", id: "compliance" },
                                { label: "8. Children's Privacy", id: "children" },
                                { label: "9. Changes to This Policy", id: "changes" },
                                { label: "10. Contact Us", id: "contact" },
                            ].map((item) => (
                                <a key={item.id} href={`#${item.id}`} className="text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                    {item.label}
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Section 1 */}
                    <section id="collect" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">1. Information We Collect</h2>

                        <h3 className="text-xl font-semibold text-slate-800 mb-3">Personal Information</h3>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            When you create an account, purchase a license, or contact our support team, we may collect the following personal information:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2 mb-6">
                            <li>Full name and email address</li>
                            <li>Company name and business address</li>
                            <li>Phone number (optional)</li>
                            <li>Billing address and payment information (processed securely via our payment provider)</li>
                            <li>WooCommerce store URL and WordPress version</li>
                        </ul>

                        <h3 className="text-xl font-semibold text-slate-800 mb-3">Usage Data</h3>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            We automatically collect certain technical data when you use our plugin or visit our website:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>IP address and approximate geographic location</li>
                            <li>Browser type, operating system, and device information</li>
                            <li>Pages visited, time spent, and navigation patterns on our website</li>
                            <li>Plugin usage analytics (feature adoption, import frequency, error logs)</li>
                            <li>Referral source and search terms used to find our site</li>
                        </ul>
                    </section>

                    {/* Section 2 */}
                    <section id="use" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">2. How We Use Your Information</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            We use the information we collect for the following purposes:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>To provide, maintain, and improve the ETIM Pro plugin and related services</li>
                            <li>To process transactions and manage your subscription</li>
                            <li>To provide customer support and respond to inquiries</li>
                            <li>To send important service-related notifications (license expiry, updates, security alerts)</li>
                            <li>To analyze usage patterns and improve user experience</li>
                            <li>To detect, prevent, and address technical issues or security threats</li>
                            <li>To send promotional communications about new features or offers (with your consent, and you can opt-out at any time)</li>
                            <li>To comply with legal obligations and enforce our Terms of Service</li>
                        </ul>
                    </section>

                    {/* Section 3 */}
                    <section id="storage" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">3. Data Storage &amp; Security</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            We take the security of your data seriously and implement industry-standard measures to protect it:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>Encryption in Transit:</strong> All data transmitted between your browser and our servers is encrypted using SSL/TLS protocols</li>
                            <li><strong>Encryption at Rest:</strong> Sensitive data stored in our databases is encrypted using AES-256 encryption</li>
                            <li><strong>Access Controls:</strong> Strict role-based access controls limit who can access personal data within our organization</li>
                            <li><strong>Regular Audits:</strong> We conduct regular security audits and vulnerability assessments</li>
                            <li><strong>Data Backups:</strong> Automated daily backups ensure data recovery in case of incidents</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            Your data is primarily stored on servers located within the European Union. We retain your personal data for as long as your account is active or as needed to provide services, comply with legal obligations, and resolve disputes.
                        </p>
                    </section>

                    {/* Section 4 */}
                    <section id="sharing" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">4. Data Sharing &amp; Disclosure</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            <strong>We do not sell your personal data.</strong> We may share your information only in the following circumstances:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>Service Providers:</strong> With trusted third-party providers who help us operate our business (payment processing, email delivery, hosting, analytics), subject to strict data processing agreements</li>
                            <li><strong>Legal Requirements:</strong> When required by law, regulation, legal process, or governmental request</li>
                            <li><strong>Business Protection:</strong> To protect the rights, property, or safety of ETIM Pro, our users, or the public</li>
                            <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets, your data may be transferred as part of the transaction</li>
                        </ul>
                    </section>

                    {/* Section 5 */}
                    <section id="rights" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">5. Your Data Protection Rights</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            Under applicable data protection laws (including GDPR), you have the following rights:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>Right of Access:</strong> You can request a copy of the personal data we hold about you</li>
                            <li><strong>Right to Rectification:</strong> You can request correction of inaccurate or incomplete personal data</li>
                            <li><strong>Right to Erasure:</strong> You can request deletion of your personal data under certain circumstances</li>
                            <li><strong>Right to Restrict Processing:</strong> You can request that we limit how we use your data</li>
                            <li><strong>Right to Data Portability:</strong> You can request your data in a structured, commonly used, machine-readable format</li>
                            <li><strong>Right to Object:</strong> You can object to our processing of your personal data for marketing purposes</li>
                            <li><strong>Right to Withdraw Consent:</strong> Where we rely on your consent, you can withdraw it at any time without affecting the lawfulness of prior processing</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            To exercise any of these rights, please contact us at{" "}
                            <a href="mailto:support@etimpro.ai" className="text-blue-600 hover:underline">support@etimpro.ai</a>.
                            We will respond to your request within 30 days.
                        </p>
                    </section>

                    {/* Section 6 */}
                    <section id="cookies" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">6. Cookies &amp; Tracking</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            Our website uses cookies and similar tracking technologies to enhance your experience. We use the following types of cookies:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>Essential Cookies:</strong> Required for basic site functionality (session management, security)</li>
                            <li><strong>Preference Cookies:</strong> Remember your settings and preferences (language, region)</li>
                            <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our website to improve our services</li>
                            <li><strong>Marketing Cookies:</strong> Used to deliver relevant advertisements (only with your explicit consent)</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            You can manage cookie preferences through your browser settings. Disabling certain cookies may affect the functionality of our website. Most browsers allow you to block or delete cookies, and you can find instructions for your specific browser in its help documentation.
                        </p>
                    </section>

                    {/* Section 7 */}
                    <section id="compliance" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">7. Compliance &amp; International Transfers</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            We are committed to complying with applicable data protection regulations:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>GDPR:</strong> As a company based in Sweden, we comply fully with the General Data Protection Regulation (EU) 2016/679</li>
                            <li><strong>CCPA:</strong> For California residents, we comply with the California Consumer Privacy Act</li>
                            <li><strong>International Transfers:</strong> Your data is primarily processed within the EU. If data is transferred outside the EU/EEA, we ensure adequate protection through Standard Contractual Clauses (SCCs) or other approved mechanisms</li>
                        </ul>
                    </section>

                    {/* Section 8 */}
                    <section id="children" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">8. Children&apos;s Privacy</h2>
                        <p className="text-slate-600 leading-relaxed">
                            ETIM Pro is a business-to-business service and is not intended for use by individuals under the age of 18. We do not knowingly collect personal data from children. If you believe we have inadvertently collected data from a minor, please contact us immediately and we will take steps to delete the information.
                        </p>
                    </section>

                    {/* Section 9 */}
                    <section id="changes" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">9. Changes to This Policy</h2>
                        <p className="text-slate-600 leading-relaxed">
                            We may update this Privacy Policy from time to time to reflect changes in our practices, technology, or legal requirements. We will notify you of any material changes by posting the updated policy on our website and, where appropriate, sending you a notification via email. We encourage you to review this page periodically to stay informed.
                        </p>
                    </section>

                    {/* Section 10 */}
                    <section id="contact" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">10. Contact Us</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please reach out to us:
                        </p>
                        <div className="bg-slate-50 rounded-xl p-6 not-prose">
                            <div className="space-y-3 text-sm text-slate-600">
                                <div><strong className="text-slate-800">Company:</strong> Things at Web Sweden AB</div>
                                <div><strong className="text-slate-800">Address:</strong> Sockerbruksgatan, 753 40 Lidk&ouml;ping, Sweden</div>
                                <div><strong className="text-slate-800">Email:</strong>{" "}
                                    <a href="mailto:support@etimpro.ai" className="text-blue-600 hover:underline">support@etimpro.ai</a>
                                </div>
                                <div><strong className="text-slate-800">Website:</strong>{" "}
                                    <Link href="/" className="text-blue-600 hover:underline">etimpro.ai</Link>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    );
}
