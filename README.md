# ETIM Pro Website

Modern SaaS marketing website for the ETIM Product Manager WooCommerce plugin.
Built with Next.js 14, App Router, Tailwind CSS, TypeScript, Shadcn UI, and Framer Motion.

## 🚀 Features

- **Modern Tech Stack**: Next.js 14 App Router, React, TypeScript
- **Beautiful UI**: Tailwind CSS combined with Shadcn UI components
- **Smooth Animations**: Framer Motion for scroll reveals and interactions
- **Fully Responsive**: Mobile-first approach for all pages
- **SEO Optimized**: Built-in Next.js metadata system per page
- **High Performance**: Optimized fonts, images, and minimal client-side JS

## 📁 Project Structure

```text
/
├── src/
│   ├── app/                    # Next.js App Router pages
│   │   ├── docs/               # Documentation
│   │   ├── features/           # Feature detail
│   │   ├── pricing/            # Pricing plans
│   │   ├── contact/            # Support options
│   │   ├── download/           # Latest version
│   │   └── changelog/          # Release history
│   ├── components/             # Reusable UI components
│   │   ├── ui/                 # Core Shadcn UI elements
│   │   ├── navbar.tsx          # Main navigation
│   │   ├── footer.tsx          # Global footer
│   │   ├── hero.tsx            # Main landing headline
│   │   ├── features.tsx        # Features grid
│   │   ├── pricing.tsx         # Pricing table
│   │   ├── faq.tsx             # Accordion FAQs
│   │   └── dashboard-preview.tsx # ETIM dashboard mockup
│   └── lib/                    # Utility functions
```

## 🛠️ Installation & Local Development

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Run the development server**
   ```bash
   npm run dev
   ```

3. **Open the browser**
   Navigate to [http://localhost:3000](http://localhost:3000)

## 📦 Production Build

To create an optimized production build:

```bash
npm run build
```

To start the production server:

```bash
npm run start
```

## 🌐 Deployment (Vercel)

The easiest way to deploy this Next.js app is to use the Vercel Platform.

1. Push your code to a GitHub/GitLab/Bitbucket repository.
2. Import the project into your Vercel dashboard.
3. Vercel will automatically detect Next.js and apply the correct build settings (`npm run build`).
4. Any pushes to the `main` branch will seamlessly trigger a redeployment.

## 📈 SEO & Performance Optimization

This project comes pre-configured for speed and structured data:

- Uses `next/font` for optimizing custom fonts.
- Includes distinct `metadata` objects exported from each page route for dynamic title/description tags.
- Follows accessible UI principles. Check color contrasts if modifying the primary theme.

