import {Config} from 'ziggy-js';

export type VatRateType =
  | 'standard_rate'
  | 'reduced_rate'
  | 'reduced_rate_alt'
  | 'super_reduced_rate';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  avatar_url?: string;
  stripe_account_active: boolean;
  roles: string[];
  vendor: {
    status: string;
    status_label: string;
    store_name: string;
    store_address: string;
    cover_image: string;
    country_code?: string;
    phone?: string;
  }
  country_code?: string;
  phone?: string;
}

export type Image = {
  id: number;
  thumb: string;
  small: string;
  large: string;
};

export type Video = {
  id: number;
  url: string;
};

export type Media = Image | Video;

export type VariationTypeOption = {
  id: number;
  name: string;
  images: Image[];
  type: VariationType
}

export type VariationType = {
  id: number;
  name: string;
  type: 'Select' | 'Radio' | 'Image';
  options: VariationTypeOption[]
}

export type Product = {
  id: number;
  title: string;
  slug: string;
  price: number;
  quantity: number;
  vat_rate_type?: string;
  net_price?: number;
  gross_price?: number;
  vat_amount?: number;
  image: string;
  images: Image[];
  videos: Video[];
  short_description: string;
  description: string;
  meta_title: string;
  meta_description: string;
  user: {
    id: number;
    name: string;
    store_name: string;
  };
  department: {
    id: number;
    name: string;
    slug: string;
  };
  variationTypes: VariationType[],
  variations: Array<{
    id: number;
    variation_type_option_ids: number[];
    quantity: number;
    price: number;
  }>
}

export type ProductListItem = {
  id: number;
  title: string;
  slug: string;
  price: number;
  net_price: number;
  vat_rate_type: string;
  quantity: number;
  image: string;
  user_id: number;
  user_name: string;
  user_store_name: string;
  department_id: number;
  department_name: string;
  department_slug: string;
}

export type CartItem = {
  id: number;
  product_id: number;
  title: string;
  slug: string;
  price: number;
  vat_rate_type: string;
  quantity: number;
  image: string;
  option_ids: Record<string, number>;
  options: VariationTypeOption[]
}

export type GroupedCartItems = {
  user: User;
  items: CartItem[];
  totalPrice: number;
  totalQuantity: number;
}

export type PaginationProps<T> = {
  data: Array<T>;
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string; label: string; active: boolean }>;
  },
  links: {
    first: string;
    last: string;
    prev: string;
    next: string;
  }
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  appName: string;
  csrf_token: string;
  error: string;
  success: string;
  successToast: {
    message: string;
    time: number;
  },
  errorToast: {
    message: string;
    time: number;
  },
  auth: {
    user: User;
  };
  ziggy: Config & { location: string };
  totalQuantity: number;
  totalPrice: number;
  miniCartItems: CartItem[];
  departments: Department[];
  keyword: string;
  countryCode: string;
};


export type OrderItem = {
  id: number;
  quantity: number;
  price: number;
  variation_type_option_ids: number[];
  product: {
    id: number;
    title: string;
    slug: string;
    description: string;
    image: string;
  }
}

export type Order = {
  id: number;
  total_price: number;
  status: string;
  created_at: string;
  vendorUser: {
    id: string;
    name: string;
    email: string;
    store_name: string;
    store_address: string;
  };
  orderItems: OrderItem[]
}

export type Vendor = {
  id: number;
  store_name: string;
  store_address: string;
}

export type Category = {
  id: number;
  name: string;
}

export type Department = {
  id: number;
  name: string;
  slug: string;
  meta_title: string;
  meta_description: string;
  categories: Category[]
}

export type Address = {
  id: number;
  user_id: number;
  country_code: string;
  full_name: string;
  phone: string;
  city: string;
  type: string;
  zipcode: string;
  address1: string;
  address2: string;
  state: string;
  default: boolean;
  delivery_instructions: string;
  country: Country;
}

export type Country = {
  code: string;
  name: string;
  active: boolean;
  states?: {
    [key: string]: string;
  }
}
