"use client";

import { Button } from "@/components/ui/button";
import { Mail, MessageSquare, Phone, MapPin, Send, Clock, ArrowRight } from "lucide-react";
import { useState } from "react";

export default function ContactPage() {
    const [formData, setFormData] = useState({
        name: "",
        email: "",
        company: "",
        subject: "",
        message: "",
    });
    const [submitted, setSubmitted] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitted(true);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
    };

    return (
        <div className="pt-16 pb-32">
            {/* Hero */}
            <div className="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white py-20 md:py-28">
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:32px_32px]"></div>
                <div className="container mx-auto max-w-4xl text-center relative z-10">
                    <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-4">
                        Get in Touch
                    </h1>
                    <p className="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto">
                        Have a question about ETIM Pro? We&apos;re here to help you get the most out of your WooCommerce catalog.
                    </p>
                </div>
            </div>

            {/* Contact Cards */}
            <div className="container mx-auto max-w-6xl px-4 -mt-12 relative z-20">
                <div className="grid md:grid-cols-3 gap-6 mb-16">
                    <div className="p-6 bg-white border border-slate-200 rounded-2xl shadow-lg hover:shadow-xl transition-shadow flex flex-col items-center text-center">
                        <div className="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                            <Mail className="h-6 w-6" />
                        </div>
                        <h3 className="font-semibold text-lg text-slate-900 mb-1">Email Support</h3>
                        <p className="text-slate-500 text-sm mb-3">Reach out to our dedicated support team.</p>
                        <a href="mailto:support@etimpro.ai" className="text-blue-600 font-medium text-sm hover:underline">
                            support@etimpro.ai
                        </a>
                    </div>
                    <div className="p-6 bg-white border border-blue-200 rounded-2xl shadow-lg hover:shadow-xl transition-shadow flex flex-col items-center text-center ring-2 ring-blue-500/20">
                        <div className="h-12 w-12 rounded-xl bg-blue-600 text-white flex items-center justify-center mb-4">
                            <MessageSquare className="h-6 w-6" />
                        </div>
                        <h3 className="font-semibold text-lg text-slate-900 mb-1">Live Chat</h3>
                        <p className="text-slate-500 text-sm mb-3">Chat with a classification expert now.</p>
                        <Button variant="default" size="sm" className="rounded-full bg-blue-600 hover:bg-blue-700 text-white px-6">
                            Start Chat
                        </Button>
                    </div>
                    <div className="p-6 bg-white border border-slate-200 rounded-2xl shadow-lg hover:shadow-xl transition-shadow flex flex-col items-center text-center">
                        <div className="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                            <Clock className="h-6 w-6" />
                        </div>
                        <h3 className="font-semibold text-lg text-slate-900 mb-1">Response Time</h3>
                        <p className="text-slate-500 text-sm mb-3">We typically respond within hours.</p>
                        <span className="text-emerald-600 font-medium text-sm">Mon - Fri, 9AM - 6PM CET</span>
                    </div>
                </div>
            </div>

            {/* Contact Form + Info */}
            <div className="container mx-auto max-w-6xl px-4">
                <div className="grid lg:grid-cols-5 gap-12">
                    {/* Form */}
                    <div className="lg:col-span-3">
                        <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 md:p-10">
                            <h2 className="text-2xl font-bold text-slate-900 mb-2">Send us a message</h2>
                            <p className="text-slate-500 mb-8">Fill out the form below and we&apos;ll get back to you as soon as possible.</p>

                            {submitted ? (
                                <div className="text-center py-16">
                                    <div className="h-16 w-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-6">
                                        <Send className="h-7 w-7" />
                                    </div>
                                    <h3 className="text-2xl font-bold text-slate-900 mb-2">Message Sent!</h3>
                                    <p className="text-slate-500 max-w-md mx-auto">
                                        Thank you for reaching out. Our team will review your message and respond within 24 hours.
                                    </p>
                                    <Button
                                        className="mt-6 rounded-full bg-blue-600 hover:bg-blue-700 text-white px-8"
                                        onClick={() => {
                                            setSubmitted(false);
                                            setFormData({ name: "", email: "", company: "", subject: "", message: "" });
                                        }}
                                    >
                                        Send Another Message
                                    </Button>
                                </div>
                            ) : (
                                <form onSubmit={handleSubmit} className="space-y-5">
                                    <div className="grid sm:grid-cols-2 gap-5">
                                        <div>
                                            <label htmlFor="name" className="block text-sm font-medium text-slate-700 mb-1.5">
                                                Full Name *
                                            </label>
                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                required
                                                value={formData.name}
                                                onChange={handleChange}
                                                placeholder="John Doe"
                                                className="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="email" className="block text-sm font-medium text-slate-700 mb-1.5">
                                                Email Address *
                                            </label>
                                            <input
                                                type="email"
                                                id="email"
                                                name="email"
                                                required
                                                value={formData.email}
                                                onChange={handleChange}
                                                placeholder="john@company.com"
                                                className="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid sm:grid-cols-2 gap-5">
                                        <div>
                                            <label htmlFor="company" className="block text-sm font-medium text-slate-700 mb-1.5">
                                                Company
                                            </label>
                                            <input
                                                type="text"
                                                id="company"
                                                name="company"
                                                value={formData.company}
                                                onChange={handleChange}
                                                placeholder="Your Company"
                                                className="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="subject" className="block text-sm font-medium text-slate-700 mb-1.5">
                                                Subject *
                                            </label>
                                            <select
                                                id="subject"
                                                name="subject"
                                                required
                                                value={formData.subject}
                                                onChange={handleChange}
                                                className="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            >
                                                <option value="">Select a topic</option>
                                                <option value="general">General Inquiry</option>
                                                <option value="sales">Sales &amp; Pricing</option>
                                                <option value="support">Technical Support</option>
                                                <option value="enterprise">Enterprise / Custom</option>
                                                <option value="demo">Request a Demo</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label htmlFor="message" className="block text-sm font-medium text-slate-700 mb-1.5">
                                            Message *
                                        </label>
                                        <textarea
                                            id="message"
                                            name="message"
                                            required
                                            rows={5}
                                            value={formData.message}
                                            onChange={handleChange}
                                            placeholder="Tell us about your project, questions, or how we can help..."
                                            className="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full sm:w-auto h-12 px-8 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-base font-medium shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all"
                                    >
                                        Send Message
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </form>
                            )}
                        </div>
                    </div>

                    {/* Sidebar Info */}
                    <div className="lg:col-span-2 space-y-8">
                        {/* Office Location */}
                        <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
                            <h3 className="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <MapPin className="h-5 w-5 text-blue-600" />
                                Our Office
                            </h3>
                            <div className="space-y-3 text-slate-600 text-sm">
                                <p className="leading-relaxed">
                                    Sockerbruksgatan<br />
                                    753140 Lidk&ouml;ping<br />
                                    Sweden
                                </p>
                            </div>
                        </div>

                        {/* Contact Details */}
                        <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
                            <h3 className="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <Phone className="h-5 w-5 text-blue-600" />
                                Contact Details
                            </h3>
                            <div className="space-y-3 text-sm">
                                <div className="flex items-center gap-3 text-slate-600">
                                    <Mail className="h-4 w-4 text-blue-500 shrink-0" />
                                    <a href="mailto:support@etimpro.ai" className="hover:text-blue-600 transition-colors">
                                        support@etimpro.ai
                                    </a>
                                </div>
                            </div>
                        </div>

                        {/* Quick Links */}
                        <div className="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-8 text-white">
                            <h3 className="text-lg font-bold mb-3">Need help right away?</h3>
                            <p className="text-blue-100 text-sm mb-5">
                                Check our documentation for instant answers to common questions.
                            </p>
                            <div className="space-y-2">
                                <a href="/docs" className="flex items-center gap-2 text-sm text-white/90 hover:text-white transition-colors">
                                    <ArrowRight className="h-3 w-3" /> Documentation
                                </a>
                                <a href="/pricing" className="flex items-center gap-2 text-sm text-white/90 hover:text-white transition-colors">
                                    <ArrowRight className="h-3 w-3" /> View Pricing
                                </a>
                                <a href="/changelog" className="flex items-center gap-2 text-sm text-white/90 hover:text-white transition-colors">
                                    <ArrowRight className="h-3 w-3" /> Changelog
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
