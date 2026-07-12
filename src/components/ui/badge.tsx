import { cn } from "@/lib/utils";

const variants = {
  verified: "bg-green-100 text-green-700",
  claimed: "bg-blue-100 text-blue-700",
  pending: "bg-yellow-100 text-yellow-700",
  paused: "bg-gray-100 text-gray-600",
  sponsored: "bg-amber-100 text-amber-700",
  category: "bg-teal-50 text-teal-700",
  country: "bg-gray-50 text-gray-500",
  default: "bg-gray-100 text-gray-600",
};

type BadgeVariant = keyof typeof variants;

export function Badge({
  variant = "default",
  className,
  children,
}: {
  variant?: BadgeVariant;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <span className={cn("inline-block px-2 py-0.5 rounded text-xs font-medium", variants[variant], className)}>
      {children}
    </span>
  );
}
