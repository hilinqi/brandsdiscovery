export const dynamic = "force-dynamic";

import Link from "next/link";
import { getPublishedBrands, getCategoryTree } from "@/lib/queries";
import { BrandCard } from "@/components/brand/BrandCard";
import { SearchBar } from "@/components/SearchBar";

const topCategories = [
  { name: "Electronics & Technology", slug: "electronics-technology", icon: "💻" },
  { name: "Home & Kitchen", slug: "home-kitchen", icon: "🏠" },
  { name: "Pet Products", slug: "pet-products", icon: "🐾" },
  { name: "Beauty & Personal Care", slug: "beauty-personal-care", icon: "💄" },
  { name: "Outdoor & Sports", slug: "outdoor-sports", icon: "🏔️" },
  { name: "Fashion & Accessories", slug: "fashion-accessories", icon: "👗" },
  { name: "Baby & Kids", slug: "baby-kids", icon: "👶" },
  { name: "Automotive", slug: "automotive", icon: "🚗" },
  { name: "Office & Productivity", slug: "office-productivity", icon: "💼" },
  { name: "Lifestyle & Gifts", slug: "lifestyle-gifts", icon: "🎁" },
];

export default async function HomePage() {
  const [{ brands: featured }, { brands: latest }] = await Promise.all([
    getPublishedBrands({ verifiedOnly: true, perPage: 8 }),
    getPublishedBrands({ orderBy: "createdAt", order: "desc", perPage: 8 }),
  ]);

  const showFeatured = featured.length > 0;
  const showLatest = latest.length > 0;

  return (
    <div>
      {/* Hero */}
      <section className="bg-gradient-to-br from-[#1B2A4A] to-[#2D4070] text-white py-16 text-center">
        <div className="max-w-7xl mx-auto px-4">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">Discover Independent Brands</h1>
          <p className="text-lg text-white/85 max-w-xl mx-auto mb-8">
            Explore unique brands from around the world. Find products you&apos;ll love and support independent creators.
          </p>
          <SearchBar />
        </div>
      </section>

      {/* Categories */}
      <section className="max-w-7xl mx-auto px-4 py-12">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Browse Categories</h2>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
          {topCategories.map((cat) => (
            <Link
              key={cat.slug}
              href={`/categories/${cat.slug}`}
              className="bg-white border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow"
            >
              <span className="text-3xl block mb-2">{cat.icon}</span>
              <span className="text-sm font-medium text-gray-700">{cat.name}</span>
            </Link>
          ))}
        </div>
      </section>

      {/* Featured Brands */}
      {showFeatured && (
        <section className="bg-white py-12">
          <div className="max-w-7xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-6">Featured Brands</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {featured.map((brand) => (
                <BrandCard key={brand.id} brand={brand} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Latest */}
      {showLatest && (
        <section className="py-12">
          <div className="max-w-7xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-6">Latest Discoveries</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {latest.map((brand) => (
                <BrandCard key={brand.id} brand={brand} />
              ))}
            </div>
            <div className="text-center mt-8">
              <Link href="/brands" className="inline-block border-2 border-[#1B2A4A] text-[#1B2A4A] px-6 py-2 rounded-lg font-medium hover:bg-[#1B2A4A] hover:text-white transition-colors">
                View All Brands →
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* Empty state */}
      {!showFeatured && !showLatest && (
        <section className="py-16 text-center">
          <h2 className="text-2xl font-bold mb-4">Brands Are Coming Soon</h2>
          <p className="text-gray-500 max-w-md mx-auto mb-6">
            We&apos;re curating the best independent brands. In the meantime, browse our categories or submit a brand you love.
          </p>
          <Link href="/submit-brand" className="inline-block bg-teal-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-teal-700 transition-colors">
            Submit a Brand
          </Link>
        </section>
      )}

      {/* CTA */}
      <section className="bg-gradient-to-br from-teal-50 to-white py-16">
        <div className="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-8">
          <div className="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <h3 className="text-xl font-bold text-[#1B2A4A] mb-2">Know a Brand We&apos;re Missing?</h3>
            <p className="text-gray-500 mb-4">Help us grow by submitting a brand or requesting one you&apos;d like to see.</p>
            <Link href="/submit-brand" className="inline-block bg-teal-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-700 transition-colors">Submit a Brand</Link>
          </div>
          <div className="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <h3 className="text-xl font-bold text-[#1B2A4A] mb-2">Looking for Something Specific?</h3>
            <p className="text-gray-500 mb-4">Can&apos;t find what you&apos;re looking for? Request a brand or product.</p>
            <Link href="/request-brand" className="inline-block bg-[#1B2A4A] text-white px-6 py-2 rounded-lg font-medium hover:bg-[#2D4070] transition-colors">Request a Brand</Link>
          </div>
        </div>
      </section>
    </div>
  );
}
