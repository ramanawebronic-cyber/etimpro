/** @type {import('next').NextConfig} */
const nextConfig = {
    output: 'export',
    images: {
        unoptimized: true, // Disable default image optimization
    },
};

export default nextConfig;
