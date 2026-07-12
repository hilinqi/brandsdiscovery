import { getCategoryBySlug, getPublishedBrands } from "@/lib/queries";
import { BrandCard } from "@/components/brand/BrandCard";
import { notFound } from "next/navigation";

export default async function CategoryPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const category = await getCategoryBySlug(slug);
  if (!category) notFound();

  const { brands } = await getPublishedBrands({ categoryId: category.id, perPage: 50 });

  return (
    <div>
      <section className="bg-gradient-to-br from-[#1B2A4A] to-[#2D4070] text-white py-12 text-center">
        <div className="max-w-7xl mx-auto px-4">
          <h1 className="text-3xl font-bold">{category.name}</h1>
          {category.description && <p className="text-white/85 mt-2 max-w-xl mx-auto">{category.description}</p>}
          <p className="text-white/60 text-sm mt-1">{category._count.brandCategories.toLocaleString()} brands</p>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-4 py-8">
        {brands.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {brands.map((brand: any) => (
              <BrandCard key={brand.id} brand={brand} />
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <h2 className="text-xl font-bold mb-2">No Brands in This Category</h2>
            <p className="text-gray-500">Check back soon — we&apos;re adding new brands every day.</p>
          </div>
        )}
      </div>
    </div>
  );
}
