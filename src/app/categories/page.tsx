import { getCategoryTree, getCategoryBySlug, getPublishedBrands } from "@/lib/queries";
import { BrandCard } from "@/components/brand/BrandCard";
import Link from "next/link";

export default async function CategoriesPage() {
  const categories = await getCategoryTree();

  return (
    <div>
      <section className="bg-gradient-to-br from-[#1B2A4A] to-[#2D4070] text-white py-12 text-center">
        <div className="max-w-7xl mx-auto px-4">
          <h1 className="text-3xl font-bold">Browse Categories</h1>
          <p className="text-white/85 mt-2">Find brands by category. Explore products and discover new favorites.</p>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {categories.length > 0 ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            {categories.map((cat: any) => (
              <Link
                key={cat.id}
                href={`/categories/${cat.slug}`}
                className="bg-white border border-gray-200 rounded-lg p-6 text-center hover:shadow-md transition-shadow"
              >
                <span className="text-3xl block mb-2">📂</span>
                <span className="text-sm font-medium text-gray-700">{cat.name}</span>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <h2 className="text-xl font-bold">Categories Coming Soon</h2>
            <p className="text-gray-500 mt-2">We&apos;re organizing our brand directory. Check back shortly!</p>
          </div>
        )}
      </div>
    </div>
  );
}
