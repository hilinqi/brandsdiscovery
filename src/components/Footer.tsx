import Link from "next/link";

const footerLinks = {
  Discover: [
    { label: "All Brands", href: "/brands" },
    { label: "Categories", href: "/categories" },
    { label: "Buying Guides", href: "/guides" },
    { label: "Search", href: "/search" },
  ],
  "For Merchants": [
    { label: "Register", href: "/merchant/register" },
    { label: "Merchant Login", href: "/merchant/login" },
    { label: "Submit Your Brand", href: "/submit-brand" },
    { label: "Partner With Us", href: "/partner" },
  ],
  Company: [
    { label: "About", href: "/about" },
    { label: "Contact", href: "/contact" },
    { label: "FAQ", href: "/faq" },
    { label: "Advertise", href: "/advertise" },
  ],
  Legal: [
    { label: "Privacy Policy", href: "/privacy-policy" },
    { label: "Terms", href: "/terms" },
    { label: "Cookie Policy", href: "/cookie-policy" },
    { label: "Affiliate Disclosure", href: "/affiliate-disclosure" },
  ],
};

export function Footer() {
  return (
    <footer className="bg-[#1B2A4A] text-white/70 mt-auto">
      <div className="max-w-7xl mx-auto px-4 py-12">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
          {Object.entries(footerLinks).map(([title, links]) => (
            <div key={title}>
              <h4 className="text-white text-sm font-semibold uppercase tracking-wider mb-3">{title}</h4>
              {links.map((link) => (
                <Link key={link.href} href={link.href} className="block text-sm py-1 hover:text-white transition-colors">
                  {link.label}
                </Link>
              ))}
            </div>
          ))}
        </div>
        <div className="border-t border-white/10 mt-8 pt-6 text-center text-xs">
          &copy; {new Date().getFullYear()} BrandsDiscovery. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
