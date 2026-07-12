export const dynamic = "force-dynamic";

import { searchAll } from "@/lib/queries";
import { BrandCard } from "@/components/brand/BrandCard";
import { SearchBar } from "@/components/SearchBar";
import Link from "next/link";

export default async function SearchPage({ searchParams }: { searchParams: Promise<{ q?: string; page?: string }> }) {
  const params = await searchParams;
  const query = params.q || "";
  const page = Math.max(1, parseInt(params.page || "1"));

  const results = query ? await searchAll(query, { page, perPage: 20 }) : null;

  return (
    <div>
      <section className="bg-gradient-to-br from-[#1B2A4A] to-[#2D4070] text-white py-8 text-center">
        <div className="max-w-7xl mx-auto px-4">
          <SearchBar defaultValue={query} />
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {results && (
          <p className="text-gray-500 mb-6">
            {(results.totalBrands + results.totalProducts).toLocaleString()} results for &quot;<strong>{query}</strong>&quot;
          </p>
        )}

        {results?.brands.length ? (
          <section className="mb-8">
            <h2 className="text-xl font-bold mb-4">Brands</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {results.brands.map((brand: any) => (
                <BrandCard key={brand.id} brand={brand} />
              ))}
            </div>
          </section>
        ) : null}

        {results?.categories.length ? (
          <section className="mb-8">
            <h2 className="text-xl font-bold mb-4">Categories</h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
              {results.categories.map((cat: any) => (
                <Link key={cat.id} href={`/categories/${cat.slug}`} className="bg-white border rounded-lg p-4 text-center hover:shadow">
                  <span className="font-medium text-gray-700">{cat.name}</span>
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        {results?.products.length ? (
          <section>
            <h2 className="text-xl font-bold mb-4">Products</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {results.products.map((product: any) => (
                <div key={product.id} className="bg-white border rounded-lg p-4">
                  <h4 className="font-medium">{product.name}</h4>
                  <span className="text-sm text-gray-400">{product.brand?.name}</span>
                </div>
              ))}
            </div>
          </section>
        ) : null}

        {results && !results.brands.length && !results.categories.length && !results.products.length && (
          <div className="text-center py-16">
            <h2 className="text-2xl font-bold mb-4">No Results Found</h2>
            <p className="text-gray-500 mb-6">Try a different search term, or browse our categories.</p>
            <Link href="/categories" className="bg-teal-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-700">Browse Categories</Link>
          </div>
        )}

        {!query && (
          <div className="text-center py-16">
            <h2 className="text-2xl font-bold mb-2">Search BrandsDiscovery</h2>
            <p className="text-gray-500">Enter a brand name, category, or product to get started.</p>
          </div>
        )}
      </div>
    </div>
  );
}
