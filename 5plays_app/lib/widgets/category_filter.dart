import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/app_provider.dart';

class CategoryFilter extends StatelessWidget {
  const CategoryFilter({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AppProvider>(
      builder: (context, provider, child) {
        if (provider.categories.isEmpty) {
          return const SizedBox.shrink();
        }

        return Container(
          height: 50,
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: provider.categories.length + 1,
            itemBuilder: (context, index) {
              if (index == 0) {
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: const Text('All'),
                    selected: provider.selectedCategory == null,
                    onSelected: (selected) {
                      if (selected) {
                        provider.filterByCategory(null);
                      }
                    },
                  ),
                );
              }

              final category = provider.categories[index - 1];
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text('${category.name} (${category.count})'),
                  selected: provider.selectedCategory == category.slug,
                  onSelected: (selected) {
                    if (selected) {
                      provider.filterByCategory(category.slug);
                    } else {
                      provider.filterByCategory(null);
                    }
                  },
                ),
              );
            },
          ),
        );
      },
    );
  }
}