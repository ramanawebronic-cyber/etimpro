import { NextResponse } from "next/server";
import nodemailer from "nodemailer";

export async function POST(request: Request) {
    try {
        const { name, email, company, subject, message } = await request.json();

        // Validate required fields
        if (!name || !email || !subject || !message) {
            return NextResponse.json(
                { error: "Please fill in all required fields." },
                { status: 400 }
            );
        }

        // Check if Email credentials are set up
        if (!process.env.GMAIL_USER || !process.env.GMAIL_APP_PASSWORD) {
            console.error("Missing GMAIL_USER or GMAIL_APP_PASSWORD in environment variables.");
            return NextResponse.json(
                { error: "SMTP Server Configuration Missing. Please check your .env.local file with valid GMAIL_USER and GMAIL_APP_PASSWORD." },
                { status: 500 }
            );
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return NextResponse.json(
                { error: "Please provide a valid email address." },
                { status: 400 }
            );
        }

        // Create Nodemailer transporter with Gmail SMTP
        const transporter = nodemailer.createTransport({
            service: "gmail",
            auth: {
                user: process.env.GMAIL_USER,
                pass: process.env.GMAIL_APP_PASSWORD,
            },
        });

        // Subject line mapping
        const subjectLabels: Record<string, string> = {
            general: "General Inquiry",
            sales: "Sales & Pricing",
            support: "Technical Support",
            enterprise: "Enterprise / Custom",
            demo: "Request a Demo",
        };

        const subjectLabel = subjectLabels[subject] || subject;

        // Build clean HTML email template
        const htmlTemplate = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #2563eb, #4338ca); border-radius: 16px 16px 0 0; padding: 32px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">
                New Contact Form Submission
            </h1>
            <p style="color: #bfdbfe; margin: 8px 0 0; font-size: 14px;">
                ETIM Pro Website
            </p>
        </div>

        <!-- Body -->
        <div style="background-color: #ffffff; padding: 32px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 16px 0; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Full Name
                        </div>
                        <div style="font-size: 16px; color: #1e293b; font-weight: 500;">
                            ${name}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 0; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Email Address
                        </div>
                        <div style="font-size: 16px; color: #2563eb;">
                            <a href="mailto:${email}" style="color: #2563eb; text-decoration: none;">${email}</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 0; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Company Name
                        </div>
                        <div style="font-size: 16px; color: #1e293b;">
                            ${company || "Not provided"}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 0; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Subject
                        </div>
                        <div style="display: inline-block; padding: 4px 12px; background-color: #eff6ff; color: #2563eb; border-radius: 20px; font-size: 14px; font-weight: 500;">
                            ${subjectLabel}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 0;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                            Message
                        </div>
                        <div style="font-size: 15px; color: #334155; line-height: 1.6; background-color: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            ${message.replace(/\n/g, "<br>")}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Reply Button -->
            <div style="text-align: center; margin-top: 24px;">
                <a href="mailto:${email}?subject=Re: ${subjectLabel} - ETIM Pro" style="display: inline-block; padding: 12px 32px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Reply to ${name}
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 24px 0; color: #94a3b8; font-size: 12px;">
            <p style="margin: 0;">This email was sent from the ETIM Pro contact form.</p>
            <p style="margin: 4px 0 0;">&copy; ${new Date().getFullYear()} ETIM Pro by Things at Web Sweden AB</p>
        </div>
    </div>
</body>
</html>`;

        // Send the email
        await transporter.sendMail({
            from: `"ETIM Pro Contact" <${process.env.GMAIL_USER}>`,
            to: "ramana.webronic@gmail.com",
            replyTo: email,
            subject: `[ETIM Pro] ${subjectLabel} — from ${name}`,
            html: htmlTemplate,
        });

        return NextResponse.json(
            { message: "Message sent successfully!" },
            { status: 200 }
        );
    } catch (error) {
        console.error("Contact form error:", error);
        return NextResponse.json(
            { error: error instanceof Error ? error.message : "Failed to send message. Please try again later." },
            { status: 500 }
        );
    }
}
