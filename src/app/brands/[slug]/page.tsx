import { getBrandBySlug } from "@/lib/queries";
import { Badge } from "@/components/ui/badge";
import { Card, CardBody } from "@/components/ui/card";
import { notFound } from "next/navigation";
import Link from "next/link";

export default async function BrandPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const brand = await getBrandBySlug(slug);
  if (!brand || brand.publicationStatus !== "published") notFound();

  const primaryCategory = brand.brandCategories.find((bc: any) => bc.isPrimary)?.category;
  const categories = brand.brandCategories.map((bc: any) => bc.category);
  const socialLinks = brand.socialLinks ? JSON.parse(brand.socialLinks) : {};
  const marketsData = brand.markets ? JSON.parse(brand.markets) : [];
  const shippingData = brand.shippingRegions ? JSON.parse(brand.shippingRegions) : [];
  const paymentData = brand.paymentMethods ? JSON.parse(brand.paymentMethods) : [];

  return (
    <div>
      {/* Cover */}
      {brand.coverKey && (
        <div className="w-full h-64 md:h-96 bg-gray-200 overflow-hidden">
          <img src={`/api/images/${brand.coverKey}`} alt="" className="w-full h-full object-cover" />
        </div>
      )}

      <div className="max-w-7xl mx-auto px-4">
        {/* Header */}
        <div className="flex flex-col md:flex-row gap-6 py-8 items-start">
          {brand.logoKey && (
            <img src={`/api/images/${brand.logoKey}`} alt={`${brand.name} logo`} className="w-32 h-32 object-contain border rounded-lg bg-white p-2" />
          )}
          <div className="flex-1">
            <h1 className="text-3xl font-bold text-gray-900">{brand.name}</h1>
            <div className="flex flex-wrap gap-2 mt-2">
              {brand.isVerified && <Badge variant="verified">✓ Verified</Badge>}
              {primaryCategory && (
                <Link href={`/categories/${primaryCategory.slug}`}><Badge variant="category">{primaryCategory.name}</Badge></Link>
              )}
              {brand.originCountry && <Badge variant="country">{brand.originCountry}</Badge>}
            </div>
            {brand.shortDescription && (
              <p className="text-lg text-gray-500 mt-3">{brand.shortDescription}</p>
            )}
            <div className="flex gap-3 mt-4 flex-wrap">
              <a
                href={`/go/${brand.id}`}
                target="_blank"
                rel="noopener noreferrer"
                className="bg-teal-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-teal-700 transition-colors"
              >
                Visit Store →
              </a>
              {brand.claimStatus === "unclaimed" && (
                <Link href={`/merchant/claim/${brand.id}`} className="border-2 border-[#1B2A4A] text-[#1B2A4A] px-6 py-2.5 rounded-lg font-medium hover:bg-[#1B2A4A] hover:text-white transition-colors">
                  Claim This Brand
                </Link>
              )}
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="grid md:grid-cols-[1fr_280px] gap-8 pb-12">
          <div>
            {brand.fullDescription && (
              <section className="mb-8">
                <h2 className="text-xl font-bold mb-4">About {brand.name}</h2>
                <div className="prose prose-gray max-w-none text-gray-600 whitespace-pre-line">{brand.fullDescription}</div>
              </section>
            )}

            {brand.products.length > 0 && (
              <section>
                <h2 className="text-xl font-bold mb-4">Products</h2>
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                  {brand.products.map((product: any) => (
                    <Card key={product.id}>
                      {product.imageKey && (
                        <div className="aspect-square bg-gray-50">
                          <img src={`/api/images/${product.imageKey}`} alt={product.name} className="w-full h-full object-cover" loading="lazy" />
                        </div>
                      )}
                      <CardBody>
                        <h4 className="font-medium text-sm">{product.name}</h4>
                        {product.price && <span className="text-sm text-gray-500">{product.price}</span>}
                      </CardBody>
                    </Card>
                  ))}
                </div>
              </section>
            )}
          </div>

          {/* Sidebar */}
          <aside className="flex flex-col gap-4">
            {shippingData.length > 0 && (
              <div className="bg-white border rounded-lg p-4">
                <h4 className="text-xs uppercase tracking-wider text-gray-400 mb-2">Shipping</h4>
                <p className="text-sm text-gray-600">{shippingData.join(", ")}</p>
              </div>
            )}
            {paymentData.length > 0 && (
              <div className="bg-white border rounded-lg p-4">
                <h4 className="text-xs uppercase tracking-wider text-gray-400 mb-2">Payment Methods</h4>
                <p className="text-sm text-gray-600">{paymentData.join(", ")}</p>
              </div>
            )}
            {Object.keys(socialLinks).length > 0 && (
              <div className="bg-white border rounded-lg p-4">
                <h4 className="text-xs uppercase tracking-wider text-gray-400 mb-2">Follow</h4>
                <div className="flex flex-wrap gap-2">
                  {Object.entries(socialLinks).map(([platform, url]: any) => (
                    <a key={platform} href={url} target="_blank" rel="noopener noreferrer" className="text-sm border px-3 py-1 rounded hover:bg-gray-50 capitalize">
                      {platform}
                    </a>
                  ))}
                </div>
              </div>
            )}
          </aside>
        </div>
      </div>
    </div>
  );
}
