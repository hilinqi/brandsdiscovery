export function cn(...classes: (string | undefined | false | null)[]) {
  return classes.filter(Boolean).join(" ");
}

export function normalizeDomain(url: string): string {
  try {
    const u = new URL(url.startsWith("http") ? url : `https://${url}`);
    return u.hostname.replace(/^www\./, "").toLowerCase();
  } catch {
    return url.toLowerCase().replace(/^www\./, "").split("/")[0];
  }
}

export function generateSlug(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    || `untitled-${Date.now()}`;
}

export function calculateCompleteness(data: Record<string, unknown>): number {
  const fields: [string, number][] = [
    ["name", 15], ["shortDescription", 10], ["fullDescription", 15],
    ["website", 10], ["originCountry", 10], ["logoKey", 10],
    ["coverKey", 5], ["supportContact", 5], ["returnPolicy", 5],
    ["socialLinks", 5], ["categories", 10],
  ];
  let score = 0;
  for (const [field, weight] of fields) {
    if (data[field]) score += weight;
  }
  return Math.min(100, score);
}
