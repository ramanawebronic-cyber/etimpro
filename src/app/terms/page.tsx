import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
    title: "Terms and Conditions | ETIM Pro",
    description: "Terms and Conditions for the ETIM Pro WooCommerce plugin and related services.",
};

export default function TermsPage() {
    return (
        <div className="pt-24 pb-16">
            {/* Header */}
            <div className="bg-gradient-to-b from-slate-50 to-white border-b border-slate-100">
                <div className="container mx-auto max-w-4xl px-4 py-16 text-center">
                    <h1 className="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 mb-4">
                        Terms and Conditions
                    </h1>
                    <p className="text-slate-500 text-lg">
                        Effective Date: January 1, 2025 &middot; Last Updated: March 1, 2026
                    </p>
                </div>
            </div>

            {/* Content */}
            <div className="container mx-auto max-w-4xl px-4 py-12">
                <div className="prose prose-slate prose-lg max-w-none">
                    {/* Quick Nav */}
                    <div className="bg-slate-50 rounded-2xl p-6 mb-12 not-prose">
                        <h3 className="font-semibold text-slate-800 mb-3 text-sm uppercase tracking-wider">Quick Navigation</h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            {[
                                { label: "1. Introduction & Service Description", id: "introduction" },
                                { label: "2. Account Registration", id: "account" },
                                { label: "3. Subscription Plans & Billing", id: "billing" },
                                { label: "4. Content & Usage Rights", id: "content" },
                                { label: "5. Usage Restrictions", id: "restrictions" },
                                { label: "6. Intellectual Property", id: "ip" },
                                { label: "7. Disclaimer & Limitation of Liability", id: "disclaimer" },
                                { label: "8. Termination", id: "termination" },
                                { label: "9. Dispute Resolution", id: "disputes" },
                                { label: "10. Contact Information", id: "contact" },
                            ].map((item) => (
                                <a key={item.id} href={`#${item.id}`} className="text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                    {item.label}
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Section 1 */}
                    <section id="introduction" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">1. Introduction &amp; Service Description</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            These Terms and Conditions (&ldquo;Terms&rdquo;) govern your use of ETIM Pro services provided by Things at Web Sweden AB (&ldquo;Company&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;, or &ldquo;our&rdquo;), a company registered in Sweden. By accessing or using our services, you agree to be bound by these Terms.
                        </p>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            ETIM Pro provides a WooCommerce plugin and related services for managing ETIM product classifications, including but not limited to:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>ETIM class, feature, and group management within WooCommerce</li>
                            <li>CSV/XML data import and bulk mapping tools</li>
                            <li>Multi-language translation support for up to 17 European languages</li>
                            <li>Automatic feature generation and product classification</li>
                            <li>Technical support and plugin updates</li>
                        </ul>
                    </section>

                    {/* Section 2 */}
                    <section id="account" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">2. Account Registration</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            To access certain features of ETIM Pro, you must create an account and purchase a valid license. You agree to:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>Provide accurate, current, and complete information during registration</li>
                            <li>Maintain the security of your account credentials</li>
                            <li>Be at least 18 years of age or the age of legal majority in your jurisdiction</li>
                            <li>Accept responsibility for all activities that occur under your account</li>
                            <li>Notify us immediately of any unauthorized access to your account</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            We reserve the right to suspend or terminate accounts that violate these Terms or engage in fraudulent activity.
                        </p>
                    </section>

                    {/* Section 3 */}
                    <section id="billing" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">3. Subscription Plans &amp; Billing</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            ETIM Pro is offered through annual subscription plans. Key billing terms include:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li><strong>Subscription Period:</strong> Licenses are valid for one (1) year from the date of purchase and include updates and support for the duration of the subscription.</li>
                            <li><strong>Renewal:</strong> Subscriptions renew automatically unless cancelled at least 30 days before the renewal date.</li>
                            <li><strong>Payment:</strong> All payments are processed securely. Prices are listed in USD and may be subject to applicable taxes.</li>
                            <li><strong>Refund Policy:</strong> We offer a 14-day money-back guarantee from the date of initial purchase. Refunds are not available after this period or for renewals. To request a refund, contact our support team.</li>
                            <li><strong>Cancellation:</strong> You may cancel your subscription at any time. Upon cancellation, you will retain access until the end of your current billing period. No partial refunds will be issued for unused time.</li>
                        </ul>
                    </section>

                    {/* Section 4 */}
                    <section id="content" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">4. Content &amp; Usage Rights</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            You retain full ownership of all product data, configurations, and content that you upload or create using ETIM Pro. You grant us a limited license to process this data solely for the purpose of providing the service.
                        </p>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            You agree not to upload, store, or transmit any content that:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>Violates any applicable local, national, or international laws</li>
                            <li>Infringes upon the intellectual property rights of any third party</li>
                            <li>Contains malicious code, viruses, or harmful components</li>
                            <li>Is fraudulent, deceptive, or misleading in nature</li>
                        </ul>
                    </section>

                    {/* Section 5 */}
                    <section id="restrictions" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">5. Usage Restrictions</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            When using ETIM Pro, you agree not to:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>Reverse engineer, decompile, disassemble, or attempt to discover the source code of the plugin</li>
                            <li>Redistribute, resell, lease, or sublicense the plugin or any part of the service</li>
                            <li>Use the plugin on more sites than permitted by your license tier</li>
                            <li>Use automated tools to scrape, mine, or extract data from the service</li>
                            <li>Circumvent or disable any security, authentication, or technical protection measures</li>
                            <li>Use the service to develop a competing product or service</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            Each subscription plan includes specific usage limits (e.g., number of WooCommerce sites). Please refer to the <Link href="/pricing" className="text-blue-600 hover:underline">Pricing page</Link> for details on plan-specific allowances.
                        </p>
                    </section>

                    {/* Section 6 */}
                    <section id="ip" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">6. Intellectual Property</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            All intellectual property rights in and to the ETIM Pro plugin, website, documentation, and related materials are owned by Things at Web Sweden AB. This includes, but is not limited to:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>The ETIM Pro brand name, logo, and associated trademarks</li>
                            <li>All software code, algorithms, and technical architecture</li>
                            <li>User interface designs, graphics, and visual elements</li>
                            <li>Documentation, tutorials, and educational content</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            ETIM classification data and standards are the property of ETIM International. ETIM Pro is a licensed tool for working with ETIM data and does not claim ownership of the ETIM standard itself.
                        </p>
                    </section>

                    {/* Section 7 */}
                    <section id="disclaimer" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">7. Disclaimer &amp; Limitation of Liability</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            ETIM Pro is provided on an &ldquo;as is&rdquo; and &ldquo;as available&rdquo; basis without warranties of any kind, either express or implied, including but not limited to warranties of merchantability, fitness for a particular purpose, or non-infringement.
                        </p>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            To the maximum extent permitted by applicable law:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>We do not guarantee uninterrupted, error-free, or secure operation of the plugin or services</li>
                            <li>Our total liability shall not exceed the amounts you have paid to us in the twelve (12) months preceding the claim</li>
                            <li>We shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, or business opportunities</li>
                        </ul>
                    </section>

                    {/* Section 8 */}
                    <section id="termination" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">8. Termination</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            We may suspend or terminate your access to ETIM Pro at our discretion if:
                        </p>
                        <ul className="list-disc pl-6 text-slate-600 space-y-2">
                            <li>You breach any provision of these Terms</li>
                            <li>Your subscription payment fails and is not resolved within a reasonable period</li>
                            <li>We are required to do so by law or regulatory authority</li>
                            <li>We reasonably believe your use poses a security risk to the service or other users</li>
                        </ul>
                        <p className="text-slate-600 leading-relaxed mt-4">
                            Upon termination, your license to use the plugin will be revoked. You may export your product data before termination takes effect. Any provisions that by their nature should survive termination (including intellectual property, limitation of liability, and dispute resolution) shall continue in effect.
                        </p>
                    </section>

                    {/* Section 9 */}
                    <section id="disputes" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">9. Dispute Resolution</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            These Terms shall be governed by and construed in accordance with the laws of Sweden, without regard to its conflict of law principles.
                        </p>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            In the event of any dispute arising out of or in connection with these Terms, the parties shall first attempt to resolve the matter through good faith negotiation. If the dispute cannot be resolved within 30 days, it shall be submitted to the exclusive jurisdiction of the courts located in Gothenburg, Sweden.
                        </p>
                    </section>

                    {/* Section 10 */}
                    <section id="contact" className="mb-12 scroll-mt-24">
                        <h2 className="text-2xl font-bold text-slate-900 mb-4">10. Contact Information</h2>
                        <p className="text-slate-600 leading-relaxed mb-4">
                            If you have any questions, concerns, or requests regarding these Terms and Conditions, please contact us:
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

                    {/* Changes Notice */}
                    <section className="mt-12 pt-8 border-t border-slate-200">
                        <p className="text-slate-500 text-sm leading-relaxed">
                            We reserve the right to modify these Terms at any time. We will notify registered users of material changes via email or through the plugin dashboard. Your continued use of ETIM Pro after such modifications constitutes acceptance of the updated Terms.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    );
}
