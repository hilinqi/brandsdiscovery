import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { Card, CardBody } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

export function BrandCardSkeleton() {
  return (
    <Card className="animate-pulse">
      <div className="aspect-[4/3] bg-gray-100 flex items-center justify-center">
        <div className="w-20 h-20 bg-gray-200 rounded" />
      </div>
      <CardBody>
        <div className="h-5 bg-gray-200 rounded w-3/4 mb-2" />
        <div className="h-4 bg-gray-100 rounded w-full mb-1" />
        <div className="h-4 bg-gray-100 rounded w-2/3" />
      </CardBody>
    </Card>
  );
}

type BrandCardData = {
  id: number;
  name: string;
  slug: string;
  logoKey?: string | null;
  coverKey?: string | null;
  shortDescription?: string | null;
  originCountry?: string | null;
  isVerified?: boolean;
};

export function BrandCard({ brand }: { brand: BrandCardData }) {
  return (
    <Card className="hover:shadow-lg transition-shadow">
      <div className="aspect-[4/3] bg-gray-50 flex items-center justify-center overflow-hidden">
        {brand.logoKey ? (
          <img src={`/api/images/${brand.logoKey}`} alt={`${brand.name} logo`} className="w-20 h-20 object-contain" loading="lazy" />
        ) : (
          <div className="w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">No Logo</div>
        )}
      </div>
      <CardBody className="flex flex-col h-full">
        <h3 className="font-semibold text-gray-900">
          <Link href={`/brands/${brand.slug}`} className="hover:text-teal-600 transition-colors">
            {brand.name}
          </Link>
        </h3>
        {brand.isVerified && <Badge variant="verified">✓ Verified</Badge>}
        {brand.shortDescription && (
          <p className="text-sm text-gray-500 mt-1 line-clamp-2">{brand.shortDescription}</p>
        )}
        <div className="mt-auto pt-2 flex items-center gap-2 text-xs text-gray-400">
          {brand.originCountry && <span>{brand.originCountry}</span>}
        </div>
      </CardBody>
      <div className="border-t px-4 py-2">
        <a
          href={`/go/${brand.id}`}
          target="_blank"
          rel="noopener noreferrer"
          className="text-sm text-teal-600 hover:text-teal-700 font-medium"
        >
          Visit Store →
        </a>
      </div>
    </Card>
  );
}
