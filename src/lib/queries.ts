import prisma from "./prisma";
import { normalizeDomain, generateSlug, calculateCompleteness } from "./utils";

export async function getPublishedBrands(args: {
  categoryId?: number;
  country?: string;
  verifiedOnly?: boolean;
  search?: string;
  orderBy?: string;
  order?: "asc" | "desc";
  page?: number;
  perPage?: number;
}) {
  const { categoryId, country, verifiedOnly, search, orderBy = "displayOrder", order = "asc", page = 1, perPage = 20 } = args;
  const where: any = { publicationStatus: "published" };

  if (categoryId) {
    where.brandCategories = { some: { categoryId } };
  }
  if (country) {
    where.originCountry = country.toUpperCase();
  }
  if (verifiedOnly) {
    where.isVerified = true;
  }
  if (search) {
    where.OR = [
      { name: { contains: search } },
      { shortDescription: { contains: search } },
      { fullDescription: { contains: search } },
    ];
  }

  const [brands, total] = await Promise.all([
    prisma.brand.findMany({
      where,
      orderBy: { [orderBy]: order },
      skip: (page - 1) * perPage,
      take: perPage,
      select: {
        id: true, name: true, slug: true, logoKey: true, coverKey: true,
        shortDescription: true, originCountry: true, isVerified: true,
        profileCompleteness: true, createdAt: true,
      },
    }),
    prisma.brand.count({ where }),
  ]);

  return { brands, total, page, perPage, pages: Math.ceil(total / perPage) };
}

export async function getBrandBySlug(slug: string) {
  return prisma.brand.findUnique({
    where: { slug },
    include: {
      brandCategories: { include: { category: true } },
      products: { where: { status: "active" }, take: 8, orderBy: { displayOrder: "asc" } },
      attributeValues: { include: { attribute: true } },
    },
  });
}

export async function getBrandById(id: number) {
  return prisma.brand.findUnique({
    where: { id },
    include: {
      brandCategories: { include: { category: true } },
      products: { where: { status: "active" }, take: 8, orderBy: { displayOrder: "asc" } },
    },
  });
}

export async function getCategoryTree() {
  return prisma.category.findMany({
    where: { status: "active" },
    orderBy: { displayOrder: "asc" },
    include: { children: { where: { status: "active" }, orderBy: { displayOrder: "asc" } } },
  });
}

export async function getCategoryBySlug(slug: string) {
  return prisma.category.findUnique({
    where: { slug },
    include: {
      _count: { select: { brandCategories: { where: { brand: { publicationStatus: "published" } } } } },
    },
  });
}

export async function searchAll(query: string, args?: { type?: string; page?: number; perPage?: number }) {
  const { type = "all", page = 1, perPage = 20 } = args || {};
  const results: any = { brands: [], categories: [], products: [], totalBrands: 0, totalCategories: 0, totalProducts: 0, query };

  if (type === "all" || type === "brands") {
    const [brands, totalBrands] = await Promise.all([
      prisma.brand.findMany({
        where: {
          publicationStatus: "published",
          OR: [{ name: { contains: query } }, { shortDescription: { contains: query } }],
        },
        orderBy: [{ isVerified: "desc" }, { profileCompleteness: "desc" }, { displayOrder: "asc" }],
        skip: (page - 1) * perPage,
        take: perPage,
        select: { id: true, name: true, slug: true, logoKey: true, shortDescription: true, originCountry: true, isVerified: true },
      }),
      prisma.brand.count({ where: { publicationStatus: "published", OR: [{ name: { contains: query } }, { shortDescription: { contains: query } }] } }),
    ]);
    results.brands = brands;
    results.totalBrands = totalBrands;
  }

  if (type === "all" || type === "categories") {
    const categories = await prisma.category.findMany({
      where: { status: "active", name: { contains: query } },
      take: 10,
    });
    results.categories = categories;
    results.totalCategories = categories.length;
  }

  if (type === "all" || type === "products") {
    const products = await prisma.product.findMany({
      where: { status: "active", name: { contains: query } },
      include: { brand: { select: { name: true } } },
      skip: (page - 1) * perPage,
      take: perPage,
    });
    results.products = products;
    results.totalProducts = products.length;
  }

  return results;
}
