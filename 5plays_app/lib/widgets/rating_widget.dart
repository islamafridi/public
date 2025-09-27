import 'package:flutter/material.dart';

class RatingWidget extends StatelessWidget {
  final double rating;
  final int votes;
  final double size;

  const RatingWidget({
    super.key,
    required this.rating,
    required this.votes,
    this.size = 16,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        ...List.generate(5, (index) {
          if (index < rating.floor()) {
            return Icon(
              Icons.star,
              size: size,
              color: Colors.amber,
            );
          } else if (index < rating) {
            return Icon(
              Icons.star_half,
              size: size,
              color: Colors.amber,
            );
          } else {
            return Icon(
              Icons.star_border,
              size: size,
              color: Colors.grey,
            );
          }
        }),
        const SizedBox(width: 4),
        Text(
          rating.toStringAsFixed(1),
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
            fontWeight: FontWeight.w500,
          ),
        ),
        Text(
          ' (${votes.toString()})',
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
            color: Theme.of(context).colorScheme.onSurfaceVariant,
          ),
        ),
      ],
    );
  }
}