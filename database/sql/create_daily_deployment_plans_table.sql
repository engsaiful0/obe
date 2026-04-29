-- Create daily_deployment_plans table
CREATE TABLE IF NOT EXISTS `daily_deployment_plans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deployment_date` DATE NOT NULL,
  `trip_time_id` BIGINT UNSIGNED NOT NULL,
  `bus_user_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `daily_deployment_plans_deployment_date_index` (`deployment_date`),
  INDEX `idx_deployment_date_trip_bus` (`deployment_date`, `trip_time_id`, `bus_user_id`),
  UNIQUE INDEX `unique_daily_deployment` (`deployment_date`, `trip_time_id`, `bus_user_id`),
  CONSTRAINT `daily_deployment_plans_trip_time_id_foreign` FOREIGN KEY (`trip_time_id`) REFERENCES `trip_times` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `daily_deployment_plans_bus_user_id_foreign` FOREIGN KEY (`bus_user_id`) REFERENCES `bus_users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `daily_deployment_plans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create daily_deployment_plan_items table
CREATE TABLE IF NOT EXISTS `daily_deployment_plan_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `daily_deployment_plan_id` BIGINT UNSIGNED NOT NULL,
  `stoppage_id` BIGINT UNSIGNED NOT NULL,
  `bus_sub_type_id` BIGINT UNSIGNED NOT NULL,
  `bus_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `daily_deployment_plan_items_daily_deployment_plan_id_index` (`daily_deployment_plan_id`),
  INDEX `idx_stoppage_bus_subtype` (`stoppage_id`, `bus_sub_type_id`),
  UNIQUE INDEX `unique_plan_stoppage_subtype` (`daily_deployment_plan_id`, `stoppage_id`, `bus_sub_type_id`),
  CONSTRAINT `daily_deployment_plan_items_daily_deployment_plan_id_foreign` FOREIGN KEY (`daily_deployment_plan_id`) REFERENCES `daily_deployment_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_deployment_plan_items_stoppage_id_foreign` FOREIGN KEY (`stoppage_id`) REFERENCES `stoppages` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `daily_deployment_plan_items_bus_sub_type_id_foreign` FOREIGN KEY (`bus_sub_type_id`) REFERENCES `bus_sub_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `daily_deployment_plan_items_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

