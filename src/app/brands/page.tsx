import { getPublishedBrands } from "@/lib/queries";
import { BrandCard } from "@/components/brand/BrandCard";
import Link from "next/link";

export default async function BrandsPage({ searchParams }: { searchParams: Promise<{ page?: string }> }) {
  const params = await searchParams;
  const page = Math.max(1, parseInt(params.page || "1"));
  const { brands, total, pages } = await getPublishedBrands({ page, perPage: 20 });

  return (
    <div>
      <section className="bg-gradient-to-br from-[#1B2A4A] to-[#2D4070] text-white py-12 text-center">
        <div className="max-w-7xl mx-auto px-4">
          <h1 className="text-3xl font-bold">All Brands</h1>
          <p className="text-white/85 mt-2">Discover independent brands from around the world.</p>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {brands.length > 0 ? (
          <>
            <p className="text-gray-500 mb-6">{total.toLocaleString()} brands found</p>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {brands.map((brand: any) => (
                <BrandCard key={brand.id} brand={brand} />
              ))}
            </div>
            {pages > 1 && (
              <div className="flex justify-center gap-2 mt-8">
                {page > 1 && <Link href={`/brands?page=${page - 1}`} className="px-4 py-2 border rounded hover:bg-gray-50">← Previous</Link>}
                <span className="px-4 py-2 text-gray-500">Page {page} of {pages}</span>
                {page < pages && <Link href={`/brands?page=${page + 1}`} className="px-4 py-2 border rounded hover:bg-gray-50">Next →</Link>}
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-16">
            <h2 className="text-2xl font-bold mb-4">No Brands Yet</h2>
            <p className="text-gray-500 mb-6">We&apos;re curating the best independent brands. Be the first to submit one!</p>
            <Link href="/submit-brand" className="bg-teal-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-700">Submit a Brand</Link>
          </div>
        )}
      </div>
    </div>
  );
}
