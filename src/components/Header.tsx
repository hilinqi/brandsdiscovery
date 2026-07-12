import Link from "next/link";
import { SearchIcon, Menu } from "lucide-react";

export function Header() {
  // Auth check deferred to client-side for MVP.
  const session = null;

  return (
    <header className="bg-[#1B2A4A] text-white sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
        <Link href="/" className="font-bold text-lg tracking-tight">
          BrandsDiscovery
        </Link>

        <nav className="hidden md:flex items-center gap-6">
          <Link href="/brands" className="text-white/85 hover:text-white text-sm font-medium transition-colors">Brands</Link>
          <Link href="/categories" className="text-white/85 hover:text-white text-sm font-medium transition-colors">Categories</Link>
          <Link href="/guides" className="text-white/85 hover:text-white text-sm font-medium transition-colors">Guides</Link>
          <Link href="/search" className="text-white/85 hover:text-white text-sm" aria-label="Search"><SearchIcon size={18} /></Link>
          <Link href="/submit-brand" className="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors">Submit Your Brand</Link>
          {session ? (
            <Link href="/account" className="text-white/85 hover:text-white text-sm">Account</Link>
          ) : (
            <Link href="/login" className="text-white/85 hover:text-white text-sm">Login</Link>
          )}
        </nav>

        <button className="md:hidden text-white" aria-label="Menu">
          <Menu size={22} />
        </button>
      </div>
    </header>
  );
}
