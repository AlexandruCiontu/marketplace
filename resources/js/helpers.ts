import {CartItem, User} from "@/types";

export const arraysAreEqual = (arr1: any[], arr2: any[]) => {
  if (arr1.length !== arr2.length) return false; // Check if lengths are the same

  return arr1.every((value, index) => value === arr2[index]); // Check each element
}

export const productRoute = (item: CartItem) => {
  const params = new URLSearchParams();
  Object.entries(item.option_ids)
    .forEach(([typeId, optionId]) => {
      params.append(`options[${typeId}]`, optionId + '')
    })

  return route('product.show', item.slug) + '?' + params.toString();
}

export const hasRole = (user: User, role: string) => {
  return user.roles.includes(role)
}

export const hasAnyRole = (user: User, roles: string[]) => {
  return roles.some(role => user.roles.includes(role))
}

export const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',  // This should ideally come from a config or context
    minimumFractionDigits: 2
  }).format(price)
}
