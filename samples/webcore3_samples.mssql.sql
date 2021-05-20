USE [master]
GO

/****** Object:  Database [webcore3_samples]    Script Date: 07/21/2009 19:22:02 ******/
CREATE DATABASE [webcore3_samples]

ALTER DATABASE [webcore3_samples] SET COMPATIBILITY_LEVEL = 100
GO

IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [webcore3_samples].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO

ALTER DATABASE [webcore3_samples] SET ANSI_NULL_DEFAULT OFF 
GO

ALTER DATABASE [webcore3_samples] SET ANSI_NULLS OFF 
GO

ALTER DATABASE [webcore3_samples] SET ANSI_PADDING OFF 
GO

ALTER DATABASE [webcore3_samples] SET ANSI_WARNINGS OFF 
GO

ALTER DATABASE [webcore3_samples] SET ARITHABORT OFF 
GO

ALTER DATABASE [webcore3_samples] SET AUTO_CLOSE OFF 
GO

ALTER DATABASE [webcore3_samples] SET AUTO_CREATE_STATISTICS ON 
GO

ALTER DATABASE [webcore3_samples] SET AUTO_SHRINK OFF 
GO

ALTER DATABASE [webcore3_samples] SET AUTO_UPDATE_STATISTICS ON 
GO

ALTER DATABASE [webcore3_samples] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO

ALTER DATABASE [webcore3_samples] SET CURSOR_DEFAULT  GLOBAL 
GO

ALTER DATABASE [webcore3_samples] SET CONCAT_NULL_YIELDS_NULL OFF 
GO

ALTER DATABASE [webcore3_samples] SET NUMERIC_ROUNDABORT OFF 
GO

ALTER DATABASE [webcore3_samples] SET QUOTED_IDENTIFIER OFF 
GO

ALTER DATABASE [webcore3_samples] SET RECURSIVE_TRIGGERS OFF 
GO

ALTER DATABASE [webcore3_samples] SET  ENABLE_BROKER 
GO

ALTER DATABASE [webcore3_samples] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO

ALTER DATABASE [webcore3_samples] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO

ALTER DATABASE [webcore3_samples] SET TRUSTWORTHY OFF 
GO

ALTER DATABASE [webcore3_samples] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO

ALTER DATABASE [webcore3_samples] SET PARAMETERIZATION SIMPLE 
GO

ALTER DATABASE [webcore3_samples] SET READ_COMMITTED_SNAPSHOT OFF 
GO

ALTER DATABASE [webcore3_samples] SET HONOR_BROKER_PRIORITY OFF 
GO

ALTER DATABASE [webcore3_samples] SET  READ_WRITE 
GO

ALTER DATABASE [webcore3_samples] SET RECOVERY FULL 
GO

ALTER DATABASE [webcore3_samples] SET  MULTI_USER 
GO

ALTER DATABASE [webcore3_samples] SET PAGE_VERIFY CHECKSUM  
GO

ALTER DATABASE [webcore3_samples] SET DB_CHAINING OFF 
GO


USE [webcore3_samples]
GO
/****** Object:  Table [dbo].[countries]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[countries](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](255) NOT NULL,
 CONSTRAINT [PK_countries] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[states]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[states](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[country_id] [int] NOT NULL,
	[abbreviation] [varchar](5) NOT NULL,
	[name] [varchar](255) NOT NULL,
 CONSTRAINT [PK_states] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[addresses]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[addresses](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[line1] [varchar](255) NOT NULL,
	[line2] [varchar](255) NOT NULL,
	[line3] [varchar](255) NOT NULL,
	[state_id] [int] NOT NULL,
	[postal_code] [varchar](10) NOT NULL,
	[directions] [varchar](255) NOT NULL,
	[fisrt_name] [varchar](255) NOT NULL,
	[last_name] [varchar](255) NOT NULL,
	[company_name] [varchar](255) NOT NULL,
	[phone_primary] [varchar](45) NOT NULL,
	[phone_office] [varchar](45) NOT NULL,
	[phone_home] [varchar](45) NOT NULL,
 CONSTRAINT [PK_addresses] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[users]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[email] [varchar](255) NOT NULL,
	[password] [varchar](255) NOT NULL,
	[name_first] [varchar](255) NOT NULL,
	[name_last] [varchar](255) NOT NULL,
	[birthdate] [datetime] NOT NULL,
	[sys_created_date] [datetime] NOT NULL,
	[sys_created_id] [int] NOT NULL,
	[sys_updated_date] [datetime] NOT NULL,
	[sys_updated_id] [int] NOT NULL,
	[image_id] [int] NULL,
	[sys_enabled] [int] NOT NULL,
 CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[orders]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[orders](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[address_shipping_id] [int] NOT NULL,
	[address_billing_id] [int] NOT NULL,
	[order_date] [datetime] NOT NULL,
	[subtotal] [decimal](18, 0) NOT NULL,
	[tax] [decimal](18, 0) NOT NULL,
	[shipping] [decimal](18, 0) NOT NULL,
	[handling] [decimal](18, 0) NOT NULL,
	[total] [decimal](18, 0) NOT NULL,
	[status_code] [varchar](9) NOT NULL,
 CONSTRAINT [PK_orders] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[transactions]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[transactions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[order_id] [int] NOT NULL,
	[trans_date] [datetime] NOT NULL,
	[trans_amount] [decimal](18, 0) NOT NULL,
	[trans_code] [varchar](45) NOT NULL,
	[trans_result] [varchar](45) NOT NULL,
	[cc_number] [varchar](45) NOT NULL,
	[cc_expdate] [varchar](45) NOT NULL,
	[cc_name] [varchar](45) NOT NULL,
	[cc_type] [varchar](45) NOT NULL,
	[cc_ccv2] [varchar](45) NOT NULL,
 CONSTRAINT [PK_transactions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[orders_tracking]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[orders_tracking](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[order_id] [int] NOT NULL,
	[status_code] [varchar](9) NOT NULL,
	[status_note] [text] NOT NULL,
	[status_date] [datetime] NOT NULL,
	[status_user_id] [int] NOT NULL,
 CONSTRAINT [PK_orders_tracking] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[invoices]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[invoices](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[invoice_code] [varchar](45) NOT NULL,
	[contributor_name] [varchar](255) NOT NULL,
	[contributor_code] [varchar](255) NOT NULL,
	[contributor_address_id] [int] NOT NULL,
	[invoice_date] [datetime] NOT NULL,
	[order_id] [int] NOT NULL,
 CONSTRAINT [PK_invoices] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[images]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[images](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[filename] [varchar](255) NOT NULL,
	[mimetype] [varchar](255) NOT NULL,
	[data] [image] NOT NULL,
	[data_length] [int] NOT NULL,
	[thumb] [image] NOT NULL,
	[thumb_length] [int] NOT NULL,
	[sys_created_date] [datetime] NOT NULL,
	[sys_created_id] [int] NOT NULL,
 CONSTRAINT [PK_images] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[categories]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[categories](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](255) NOT NULL,
 CONSTRAINT [PK_categories] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[roles]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](255) NOT NULL,
	[description] [varchar](255) NOT NULL,
 CONSTRAINT [PK_roles] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[users_roles]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[users_roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[role_id] [int] NOT NULL,
 CONSTRAINT [PK_users_roles] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[products]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[products](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[code] [varchar](45) NOT NULL,
	[title] [varchar](255) NOT NULL,
	[name] [varchar](45) NOT NULL,
	[highlights] [text] NOT NULL,
	[price] [decimal](18, 0) NOT NULL,
	[category_id] [int] NOT NULL,
	[stock] [int] NOT NULL,
	[enabled] [int] NOT NULL,
 CONSTRAINT [PK_products] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[products_stock_tracking]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[products_stock_tracking](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[product_id] [int] NOT NULL,
	[change_stock] [int] NOT NULL,
	[change_date] [datetime] NOT NULL,
	[change_user_id] [int] NOT NULL,
	[new_stock] [int] NOT NULL,
	[user_comment] [text] NOT NULL,
	[order_id] [int] NULL,
 CONSTRAINT [PK_products_stock_tracking] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[products_images]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[products_images](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[product_id] [int] NOT NULL,
	[image_id] [int] NOT NULL,
 CONSTRAINT [PK_products_images] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[orders_products]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[orders_products](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[order_id] [int] NOT NULL,
	[product_id] [int] NOT NULL,
	[quantity] [int] NOT NULL,
	[unit_price] [decimal](18, 0) NOT NULL,
	[subtotal] [decimal](18, 0) NOT NULL,
 CONSTRAINT [PK_orders_products] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[users_addresses]    Script Date: 07/21/2009 19:21:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[users_addresses](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[address_id] [int] NOT NULL,
	[address_type] [varchar](8) NOT NULL,
 CONSTRAINT [PK_users_addresses] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  ForeignKey [FK_state_id_REL_id_292071857]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[addresses]  WITH NOCHECK ADD  CONSTRAINT [FK_state_id_REL_id_292071857] FOREIGN KEY([state_id])
REFERENCES [dbo].[states] ([id])
GO
ALTER TABLE [dbo].[addresses] CHECK CONSTRAINT [FK_state_id_REL_id_292071857]
GO
/****** Object:  ForeignKey [FK_sys_created_id_REL_id_471432695]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[images]  WITH NOCHECK ADD  CONSTRAINT [FK_sys_created_id_REL_id_471432695] FOREIGN KEY([sys_created_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[images] CHECK CONSTRAINT [FK_sys_created_id_REL_id_471432695]
GO
/****** Object:  ForeignKey [FK_order_id_REL_id_605953324]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[invoices]  WITH NOCHECK ADD  CONSTRAINT [FK_order_id_REL_id_605953324] FOREIGN KEY([order_id])
REFERENCES [dbo].[orders] ([id])
GO
ALTER TABLE [dbo].[invoices] CHECK CONSTRAINT [FK_order_id_REL_id_605953324]
GO
/****** Object:  ForeignKey [FK_address_billing_id_REL_id_532051465]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders]  WITH NOCHECK ADD  CONSTRAINT [FK_address_billing_id_REL_id_532051465] FOREIGN KEY([address_billing_id])
REFERENCES [dbo].[addresses] ([id])
GO
ALTER TABLE [dbo].[orders] CHECK CONSTRAINT [FK_address_billing_id_REL_id_532051465]
GO
/****** Object:  ForeignKey [FK_address_shipping_id_REL_id_397530836]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders]  WITH NOCHECK ADD  CONSTRAINT [FK_address_shipping_id_REL_id_397530836] FOREIGN KEY([address_shipping_id])
REFERENCES [dbo].[addresses] ([id])
GO
ALTER TABLE [dbo].[orders] CHECK CONSTRAINT [FK_address_shipping_id_REL_id_397530836]
GO
/****** Object:  ForeignKey [FK_user_id_REL_id_263010208]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders]  WITH NOCHECK ADD  CONSTRAINT [FK_user_id_REL_id_263010208] FOREIGN KEY([user_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[orders] CHECK CONSTRAINT [FK_user_id_REL_id_263010208]
GO
/****** Object:  ForeignKey [FK_order_id_REL_id_144268139]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders_products]  WITH NOCHECK ADD  CONSTRAINT [FK_order_id_REL_id_144268139] FOREIGN KEY([order_id])
REFERENCES [dbo].[orders] ([id])
GO
ALTER TABLE [dbo].[orders_products] CHECK CONSTRAINT [FK_order_id_REL_id_144268139]
GO
/****** Object:  ForeignKey [FK_product_id_REL_id_801092722]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders_products]  WITH NOCHECK ADD  CONSTRAINT [FK_product_id_REL_id_801092722] FOREIGN KEY([product_id])
REFERENCES [dbo].[products] ([id])
GO
ALTER TABLE [dbo].[orders_products] CHECK CONSTRAINT [FK_product_id_REL_id_801092722]
GO
/****** Object:  ForeignKey [FK_order_id_REL_id_935613351]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders_tracking]  WITH NOCHECK ADD  CONSTRAINT [FK_order_id_REL_id_935613351] FOREIGN KEY([order_id])
REFERENCES [dbo].[orders] ([id])
GO
ALTER TABLE [dbo].[orders_tracking] CHECK CONSTRAINT [FK_order_id_REL_id_935613351]
GO
/****** Object:  ForeignKey [FK_status_user_id_REL_id_70366280]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[orders_tracking]  WITH NOCHECK ADD  CONSTRAINT [FK_status_user_id_REL_id_70366280] FOREIGN KEY([status_user_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[orders_tracking] CHECK CONSTRAINT [FK_status_user_id_REL_id_70366280]
GO
/****** Object:  ForeignKey [FK_category_id_REL_id_204886909]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products]  WITH NOCHECK ADD  CONSTRAINT [FK_category_id_REL_id_204886909] FOREIGN KEY([category_id])
REFERENCES [dbo].[categories] ([id])
GO
ALTER TABLE [dbo].[products] CHECK CONSTRAINT [FK_category_id_REL_id_204886909]
GO
/****** Object:  ForeignKey [FK_image_id_REL_id_518768376]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products_images]  WITH NOCHECK ADD  CONSTRAINT [FK_image_id_REL_id_518768376] FOREIGN KEY([image_id])
REFERENCES [dbo].[images] ([id])
GO
ALTER TABLE [dbo].[products_images] CHECK CONSTRAINT [FK_image_id_REL_id_518768376]
GO
/****** Object:  ForeignKey [FK_product_id_REL_id_861711492]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products_images]  WITH NOCHECK ADD  CONSTRAINT [FK_product_id_REL_id_861711492] FOREIGN KEY([product_id])
REFERENCES [dbo].[products] ([id])
GO
ALTER TABLE [dbo].[products_images] CHECK CONSTRAINT [FK_product_id_REL_id_861711492]
GO
/****** Object:  ForeignKey [FK_change_user_id_REL_id_265505678]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products_stock_tracking]  WITH NOCHECK ADD  CONSTRAINT [FK_change_user_id_REL_id_265505678] FOREIGN KEY([change_user_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[products_stock_tracking] CHECK CONSTRAINT [FK_change_user_id_REL_id_265505678]
GO
/****** Object:  ForeignKey [FK_order_id_REL_id_400026307]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products_stock_tracking]  WITH NOCHECK ADD  CONSTRAINT [FK_order_id_REL_id_400026307] FOREIGN KEY([order_id])
REFERENCES [dbo].[orders] ([id])
GO
ALTER TABLE [dbo].[products_stock_tracking] CHECK CONSTRAINT [FK_order_id_REL_id_400026307]
GO
/****** Object:  ForeignKey [FK_product_id_REL_id_130985050]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[products_stock_tracking]  WITH NOCHECK ADD  CONSTRAINT [FK_product_id_REL_id_130985050] FOREIGN KEY([product_id])
REFERENCES [dbo].[products] ([id])
GO
ALTER TABLE [dbo].[products_stock_tracking] CHECK CONSTRAINT [FK_product_id_REL_id_130985050]
GO
/****** Object:  ForeignKey [FK_country_id_REL_id_534546936]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[states]  WITH NOCHECK ADD  CONSTRAINT [FK_country_id_REL_id_534546936] FOREIGN KEY([country_id])
REFERENCES [dbo].[countries] ([id])
GO
ALTER TABLE [dbo].[states] CHECK CONSTRAINT [FK_country_id_REL_id_534546936]
GO
/****** Object:  ForeignKey [FK_order_id_REL_id_191603820]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[transactions]  WITH NOCHECK ADD  CONSTRAINT [FK_order_id_REL_id_191603820] FOREIGN KEY([order_id])
REFERENCES [dbo].[orders] ([id])
GO
ALTER TABLE [dbo].[transactions] CHECK CONSTRAINT [FK_order_id_REL_id_191603820]
GO
/****** Object:  ForeignKey [FK_image_id_REL_id_326124448]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[users]  WITH NOCHECK ADD  CONSTRAINT [FK_image_id_REL_id_326124448] FOREIGN KEY([image_id])
REFERENCES [dbo].[images] ([id])
GO
ALTER TABLE [dbo].[users] CHECK CONSTRAINT [FK_image_id_REL_id_326124448]
GO
/****** Object:  ForeignKey [FK_address_id_REL_id_595165705]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[users_addresses]  WITH NOCHECK ADD  CONSTRAINT [FK_address_id_REL_id_595165705] FOREIGN KEY([address_id])
REFERENCES [dbo].[addresses] ([id])
GO
ALTER TABLE [dbo].[users_addresses] CHECK CONSTRAINT [FK_address_id_REL_id_595165705]
GO
/****** Object:  ForeignKey [FK_user_id_REL_id_460645077]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[users_addresses]  WITH NOCHECK ADD  CONSTRAINT [FK_user_id_REL_id_460645077] FOREIGN KEY([user_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[users_addresses] CHECK CONSTRAINT [FK_user_id_REL_id_460645077]
GO
/****** Object:  ForeignKey [FK_role_id_REL_id_864206962]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[users_roles]  WITH NOCHECK ADD  CONSTRAINT [FK_role_id_REL_id_864206962] FOREIGN KEY([role_id])
REFERENCES [dbo].[roles] ([id])
GO
ALTER TABLE [dbo].[users_roles] CHECK CONSTRAINT [FK_role_id_REL_id_864206962]
GO
/****** Object:  ForeignKey [FK_user_id_REL_id_729686334]    Script Date: 07/21/2009 19:21:44 ******/
ALTER TABLE [dbo].[users_roles]  WITH NOCHECK ADD  CONSTRAINT [FK_user_id_REL_id_729686334] FOREIGN KEY([user_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[users_roles] CHECK CONSTRAINT [FK_user_id_REL_id_729686334]
GO

CREATE PROCEDURE sp_ConstraintState  
              @TblName   VARCHAR(128), 
           @State BIT = 1 
AS 
DECLARE @SQLState VARCHAR(500) 
IF @State = 0 
        BEGIN 
             SET @SQLState = 'ALTER TABLE '+ @TblName + ' NOCHECK CONSTRAINT ALL' 
     END 
ELSE 
   BEGIN 
                    SET @SQLState = 'ALTER TABLE ' + @TblName + ' CHECK CONSTRAINT ALL' 
   END 
EXEC (@SQLState) 
go 

exec sp_MsForEachTable 'sp_ConstraintState ''?'',0'
GO

--countries
SET IDENTITY_INSERT countries ON 
INSERT INTO countries (id,name)
 VALUES ('1','México'); 
INSERT INTO countries (id,name)
 VALUES ('2','United States'); 
SET IDENTITY_INSERT countries OFF 
--images
SET IDENTITY_INSERT images ON 
SET IDENTITY_INSERT images OFF 
--invoices
SET IDENTITY_INSERT invoices ON 
SET IDENTITY_INSERT invoices OFF 
--orders
SET IDENTITY_INSERT orders ON 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('1','20','4','4','01/01/2009 12:00:00','101','12','0','2','115','Rejected'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('2','20','4','4','01/15/2009 12:00:00','29','3','0','1','33','Delivered'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('3','20','4','4','02/01/2009 12:00:00','13','1','0','1','15','Shipped'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('4','21','3','3','02/15/2009 12:00:00','52','6','0','1','59','Rejected'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('5','21','3','3','03/01/2009 12:00:00','34','4','0','1','39','Shipped'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('6','21','3','3','03/15/2009 12:00:00','149','17','0','2','168','Rejected'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('7','21','3','3','04/15/2009 12:00:00','65','7','0','1','73','Delivered'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('8','22','3','3','05/01/2009 12:00:00','209','25','0','4','238','Shipped'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('9','23','5','5','05/15/2009 12:00:00','106','12','0','2','120','Rejected'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('10','23','5','5','05/01/2009 12:00:00','281','33','0','4','318','Shipped'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('11','24','5','5','06/15/2009 12:00:00','37','4','0','1','42','Shipped'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('12','25','2','1','07/01/2009 12:00:00','5','0','0','1','6','Rejected'); 
INSERT INTO orders (id,user_id,address_shipping_id,address_billing_id,order_date,subtotal,tax,shipping,handling,total,status_code)
 VALUES ('13','25','1','2','07/15/2009 12:00:00','58','6','20','1','85','Delivered'); 
SET IDENTITY_INSERT orders OFF 
--orders_products
SET IDENTITY_INSERT orders_products ON 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('1','1','1','1','1','1'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('2','2','2','2','2','4'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('3','3','3','3','3','9'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('4','4','4','4','4','16'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('5','5','5','5','5','25'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('6','6','6','6','6','36'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('7','7','7','7','7','49'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('8','8','8','8','8','64'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('9','9','9','9','9','81'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('10','10','10','10','10','100'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('11','11','1','1','1','1'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('12','12','2','2','2','4'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('13','13','3','3','3','9'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('14','2','5','5','5','25'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('15','4','6','6','6','36'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('16','6','7','7','7','49'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('17','8','8','8','8','64'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('18','10','9','9','9','81'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('19','1','10','10','10','100'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('20','12','1','1','1','1'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('21','3','2','2','2','4'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('22','5','3','3','3','9'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('23','7','4','4','4','16'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('24','9','5','5','5','25'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('25','11','6','6','6','36'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('26','13','7','7','7','49'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('27','6','8','8','8','64'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('28','8','9','9','9','81'); 
INSERT INTO orders_products (id,order_id,product_id,quantity,unit_price,subtotal)
 VALUES ('29','10','10','10','10','100'); 
SET IDENTITY_INSERT orders_products OFF 
--orders_tracking
SET IDENTITY_INSERT orders_tracking ON 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('1','1','Ordered','Order was placed, proceeding to check out','07/17/2009 07:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('2','1','Rejected','Transaction Rejected','07/17/2009 08:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('3','2','Ordered','Order was placed, proceeding to check out','07/17/2009 09:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('4','2','Charged','Transaction OK','07/17/2009 10:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('5','3','Ordered','Order was placed, proceeding to check out','07/17/2009 11:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('6','3','Charged','Transaction OK','07/18/2009 12:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('7','4','Ordered','Order was placed, proceeding to check out','07/18/2009 01:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('8','4','Rejected','Transaction Rejected','07/18/2009 02:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('9','5','Ordered','Order was placed, proceeding to check out','07/18/2009 03:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('10','5','Charged','Transaction OK','07/18/2009 04:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('11','6','Ordered','Order was placed, proceeding to check out','07/18/2009 05:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('12','6','Rejected','Transaction Rejected','07/18/2009 06:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('13','7','Ordered','Order was placed, proceeding to check out','07/18/2009 07:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('14','7','Charged','Transaction OK','07/18/2009 08:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('15','8','Ordered','Order was placed, proceeding to check out','07/18/2009 09:27:44','22'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('16','8','Charged','Transaction OK','07/18/2009 10:27:44','22'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('17','9','Ordered','Order was placed, proceeding to check out','07/18/2009 11:27:44','23'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('18','9','Rejected','Transaction Rejected','07/18/2009 12:27:44','23'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('19','10','Ordered','Order was placed, proceeding to check out','07/18/2009 01:27:44','23'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('20','10','Charged','Transaction OK','07/18/2009 02:27:44','23'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('21','11','Ordered','Order was placed, proceeding to check out','07/18/2009 03:27:44','24'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('22','11','Charged','Transaction OK','07/18/2009 04:27:44','24'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('23','12','Ordered','Order was placed, proceeding to check out','07/18/2009 05:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('24','12','Rejected','Order was placed, proceeding to check out','07/18/2009 06:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('25','13','Ordered','Order was placed, proceeding to check out','07/18/2009 07:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('26','13','Charged','Transaction OK','07/18/2009 08:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('27','2','Shipped','Sent via FedEx 392039499','07/18/2009 09:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('28','7','Shipped','Sent via FedEx 392039499','07/18/2009 10:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('29','13','Shipped','Sent via FedEx 392039499','07/18/2009 11:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('30','2','Delivered','Delivered','07/19/2009 12:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('31','7','Delivered','Delivered','07/19/2009 01:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('32','13','Delivered','Delivered','07/19/2009 02:27:44','25'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('33','3','Shipped','Sent via UPS 3274823388','07/19/2009 03:27:44','20'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('34','5','Shipped','Send via UPS 92349239934','07/19/2009 04:27:44','21'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('35','8','Shipped','Sent via UPS 4345734858','07/19/2009 05:27:44','22'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('36','10','Shipped','Sent via UPS 238749239','07/19/2009 06:27:44','23'); 
INSERT INTO orders_tracking (id,order_id,status_code,status_note,status_date,status_user_id)
 VALUES ('37','11','Shipped','Sent via UPS 437238488458','07/19/2009 07:27:44','24'); 
SET IDENTITY_INSERT orders_tracking OFF 
--products
SET IDENTITY_INSERT products ON 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('1','C-AC-PCH-003-005','Aroma Candle','Peach, 3x5','-- This product is pending a description','1','1','99','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('2','C-AC-CIN-003-005','Aroma Candle','Cinnamon, 3x5','-- This product is pending a description','2','1','96','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('3','C-AC-VAN-003-005','Aroma Candle','Vanilla, 3x5','-- This product is pending a description','3','1','91','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('4','C-AC-CHO-003-005','Aroma Candle','Chocolate, 3x5','-- This product is pending a description','4','1','96','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('5','C-AC-CAR-003-005','Aroma Candle','Caramel, 3x5','-- This product is pending a description','5','1','90','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('6','H-AC-P00-SML-000','Colonial Difuser','Peach, Small','-- This product is pending a description','6','2','94','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('7','H-CD-S00-LRG-000','Colonial Difuser','Strawberry, Large','-- This product is pending a description','7','2','86','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('8','H-CD-C00-MED-000','Colonial Difuser','Caramel, Medium','-- This product is pending a description','8','2','84','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('9','H-CD-CI0-003-005','Colonial Difuser','Cinnamon, 3x5','-- This product is pending a description','9','2','82','1'); 
INSERT INTO products (id,code,title,name,highlights,price,category_id,stock,enabled)
 VALUES ('10','H-CD-V00-004-004','Colonial Difuser','Vanilla, 4x4','-- This product is pending a description','10','2','80','1'); 
SET IDENTITY_INSERT products OFF 
--products_images
SET IDENTITY_INSERT products_images ON 
SET IDENTITY_INSERT products_images OFF 
--products_stock_tracking
SET IDENTITY_INSERT products_stock_tracking ON 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('1','1','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('2','2','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('3','3','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('4','4','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('5','5','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('6','6','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('7','7','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('8','8','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('9','9','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('10','10','100','07/14/2009 07:15:44','3','100','Received by UPS tracking number 2372348298798374',NULL); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('42','1','-1','06/15/2009 12:00:00','24','99','Automatically updated.','11'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('43','2','-2','02/01/2009 12:00:00','20','98','Automatically updated.','3'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('44','2','-2','01/15/2009 12:00:00','20','96','Automatically updated.','2'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('45','3','-3','07/15/2009 12:00:00','25','97','Automatically updated.','13'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('46','3','-3','03/01/2009 12:00:00','21','94','Automatically updated.','5'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('47','3','-3','02/01/2009 12:00:00','20','91','Automatically updated.','3'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('48','4','-4','04/15/2009 12:00:00','21','96','Automatically updated.','7'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('49','5','-5','03/01/2009 12:00:00','21','95','Automatically updated.','5'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('50','5','-5','01/15/2009 12:00:00','20','90','Automatically updated.','2'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('51','6','-6','06/15/2009 12:00:00','24','94','Automatically updated.','11'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('52','7','-7','07/15/2009 12:00:00','25','93','Automatically updated.','13'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('53','7','-7','04/15/2009 12:00:00','21','86','Automatically updated.','7'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('54','8','-8','05/01/2009 12:00:00','22','92','Automatically updated.','8'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('55','8','-8','05/01/2009 12:00:00','22','84','Automatically updated.','8'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('56','9','-9','05/01/2009 12:00:00','23','91','Automatically updated.','10'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('57','9','-9','05/01/2009 12:00:00','22','82','Automatically updated.','8'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('58','10','-10','05/01/2009 12:00:00','23','90','Automatically updated.','10'); 
INSERT INTO products_stock_tracking (id,product_id,change_stock,change_date,change_user_id,new_stock,user_comment,order_id)
 VALUES ('59','10','-10','05/01/2009 12:00:00','23','80','Automatically updated.','10'); 
SET IDENTITY_INSERT products_stock_tracking OFF 
--roles
SET IDENTITY_INSERT roles ON 
INSERT INTO roles (id,name,description)
 VALUES ('1','System Administrator','Manages users passwords and catalogs'); 
INSERT INTO roles (id,name,description)
 VALUES ('2','Store Administrator','Manages Products, categories and orders'); 
INSERT INTO roles (id,name,description)
 VALUES ('3','Customer','Allows a user to place orders in the store'); 
SET IDENTITY_INSERT roles OFF 
--states
SET IDENTITY_INSERT states ON 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('1','1','AGS','Aguascalientes'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('2','1','BC','Baja California'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('3','1','BCS','Baja California Sur'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('4','1','CAM','Campeche'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('5','1','CHP','Chiapas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('6','1','CIH','Chihuahua'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('7','1','COA','Coahuila'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('8','1','COL','Colima'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('9','1','DF','Distrito Federal'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('10','1','DUR','Durango'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('11','1','MEX','Estado de México'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('12','1','GUA','Guanajuato'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('13','1','GUE','Guerrero'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('14','1','HGO','Hidalgo'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('15','1','JAL','Jalisco'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('16','1','MIH','Michoacán'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('17','1','MOR','Morelos'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('18','1','NAY','Nayarit'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('19','1','NL','Nuevo León'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('20','1','OAX','Oaxaca'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('21','1','PUE','Puebla'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('22','1','QRO','Querétaro'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('23','1','QUI','Quintana Roo'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('24','1','SLP','San Luis Potosí'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('25','1','SIN','Sinaloa'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('26','1','SON','Sonora'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('27','1','TAB','Tabasco'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('28','1','TMP','Tamaulipas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('29','1','TLX','Tlaxcala'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('30','1','VER','Veracruz'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('31','1','YUC','Yucatán'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('32','1','ZAC','Zacatecas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('33','2','WY','Wyoming'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('34','2','AL','Alabama'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('35','2','AK','Alaska'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('36','2','AS','American Samoa'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('37','2','AZ','Arizona '); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('38','2','AR','Arkansas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('39','2','CA','California '); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('40','2','CO','Colorado '); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('41','2','CT','Connecticut'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('42','2','DE','Delaware'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('43','2','DC','District Of Columbia'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('44','2','FM','Federated States Of Micronesia'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('45','2','FL','Florida'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('46','2','GA','Georgia'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('47','2','GU','Guam '); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('48','2','HI','Hawaii'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('49','2','ID','Idaho'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('50','2','IL','Illinois'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('51','2','IN','Indiana'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('52','2','IA','Iowa'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('53','2','KS','Kansas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('54','2','KY','Kentucky'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('55','2','LA','Louisiana'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('56','2','ME','Maine'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('57','2','MH','Marshall Islands'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('58','2','MD','Maryland'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('59','2','MA','Massachusetts'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('60','2','MI','Michigan'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('61','2','MN','Minnesota'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('62','2','MS','Mississippi'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('63','2','MO','Missouri'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('64','2','MT','Montana'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('65','2','NE','Nebraska'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('66','2','NV','Nevada'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('67','2','NH','New Hampshire'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('68','2','NJ','New Jersey'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('69','2','NM','New Mexico'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('70','2','NY','New York'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('71','2','NC','North Carolina'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('72','2','ND','North Dakota'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('73','2','MP','Northern Mariana Islands'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('74','2','OH','Ohio'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('75','2','OK','Oklahoma'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('76','2','OR','Oregon'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('77','2','PW','Palau'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('78','2','PA','Pennsylvania'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('79','2','PR','Puerto Rico'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('80','2','RI','Rhode Island'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('81','2','SC','South Carolina'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('82','2','SD','South Dakota'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('83','2','TN','Tennessee'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('84','2','TX','Texas'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('85','2','UT','Utah'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('86','2','VT','Vermont'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('87','2','VI','Virgin Islands'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('88','2','VA','Virginia '); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('89','2','WA','Washington'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('90','2','WV','West Virginia'); 
INSERT INTO states (id,country_id,abbreviation,name)
 VALUES ('91','2','WI','Wisconsin'); 
SET IDENTITY_INSERT states OFF 
--transactions
SET IDENTITY_INSERT transactions ON 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('1','1','01/01/2009 12:00:00','115','39821349189199929','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('2','2','01/15/2009 12:00:00','33','42309483294823099','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('3','3','02/01/2009 12:00:00','15','23842348230948858','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('4','4','02/15/2009 12:00:00','59','23948230498243908','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('5','5','03/01/2009 12:00:00','39','23654020034980093','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('6','6','03/15/2009 12:00:00','168','23940920702384857','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('7','7','04/15/2009 12:00:00','73','23948021023948540','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('8','8','05/01/2009 12:00:00','238','98793248723988374','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('9','9','05/15/2009 12:00:00','120','23984916324734658','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('10','10','05/01/2009 12:00:00','318','42983723478747777','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('11','11','06/15/2009 12:00:00','42','28748272565645552','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('12','12','07/01/2009 12:00:00','6','23984563466366465','Failure','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
INSERT INTO transactions (id,order_id,trans_date,trans_amount,trans_code,trans_result,cc_number,cc_expdate,cc_name,cc_type,cc_ccv2)
 VALUES ('13','13','07/15/2009 12:00:00','85','32428320293848588','Success','4111111111111111','2013-06-02','Individual M. Testvisa','Visa','111'); 
SET IDENTITY_INSERT transactions OFF 
--users
SET IDENTITY_INSERT users ON 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('1','admin@unosquare.com','test','System','Administrator','10/09/1982 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('2','customer@unosquare.com','test','Joe','Doe','06/19/1984 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('3','store@unosquare.com','test','Store','Administrator','07/12/1981 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('20','bart@thesimpsons.com','test','Bartolomeo','Simpson','07/12/1980 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('21','peter@familyguy.com','test','Peter','Griffin','06/11/1979 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('22','tom@familyguy.com','test','Thomas','Tucker','07/12/1981 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('23','cartman@southparkstudios.com','test','Eric','Cartman','06/11/1979 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('24','kyle@southparkstudios.com','test','Kyle','Rafalowsky','01/13/1974 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('25','stan@southparkstudios.com','test','Stanley','Marsh','12/12/1976 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
INSERT INTO users (id,email,password,name_first,name_last,birthdate,sys_created_date,sys_created_id,sys_updated_date,sys_updated_id,image_id,sys_enabled)
 VALUES ('26','kenny@southparkstudios.com','test','Kenny','McCormick','12/20/1979 12:00:00','07/14/2009 07:15:44','0','07/14/2009 07:15:44','0',NULL,'1'); 
SET IDENTITY_INSERT users OFF 
--users_addresses
SET IDENTITY_INSERT users_addresses ON 
INSERT INTO users_addresses (id,user_id,address_id,address_type)
 VALUES ('1','1','1','billing'); 
SET IDENTITY_INSERT users_addresses OFF 
--users_roles
SET IDENTITY_INSERT users_roles ON 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('1','1','1'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('5','2','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('6','3','2'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('7','20','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('8','21','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('9','22','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('10','23','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('11','24','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('12','25','3'); 
INSERT INTO users_roles (id,user_id,role_id)
 VALUES ('13','26','3'); 
SET IDENTITY_INSERT users_roles OFF 
--addresses
SET IDENTITY_INSERT addresses ON 
INSERT INTO addresses (id,line1,line2,line3,state_id,postal_code,directions,fisrt_name,last_name,company_name,phone_primary,phone_office,phone_home)
 VALUES ('1','Americas #1600','Piso 4','Col. Country Club','15','44637','','Mario','Di Vece','Unosquare S.A. de C.V.','523336789139','523336789139','523336789139'); 
INSERT INTO addresses (id,line1,line2,line3,state_id,postal_code,directions,fisrt_name,last_name,company_name,phone_primary,phone_office,phone_home)
 VALUES ('2','1001 SW Fifth Avenue','Suite 1100','','76','97204','','Michael','Barrett','Unosquare, Inc.','5035358084','5035358084','5035358084'); 
INSERT INTO addresses (id,line1,line2,line3,state_id,postal_code,directions,fisrt_name,last_name,company_name,phone_primary,phone_office,phone_home)
 VALUES ('3','100 Spooner','','','80','78920','','Peter','Griffin','','5555555555','5555555555','5555555555'); 
INSERT INTO addresses (id,line1,line2,line3,state_id,postal_code,directions,fisrt_name,last_name,company_name,phone_primary,phone_office,phone_home)
 VALUES ('4','567 Evergreen Terrace','','','76','28839','','Homer','Simpson','','5555555555','5555555555','5555555555'); 
INSERT INTO addresses (id,line1,line2,line3,state_id,postal_code,directions,fisrt_name,last_name,company_name,phone_primary,phone_office,phone_home)
 VALUES ('5','120 Platt','','','40','29938','','Liane','Cartman','','5555555555','5555555555','5555555555'); 
SET IDENTITY_INSERT addresses OFF 
--categories
SET IDENTITY_INSERT categories ON 
INSERT INTO categories (id,name)
 VALUES ('1','Specialty Candles'); 
INSERT INTO categories (id,name)
 VALUES ('2','Home Accessories'); 
INSERT INTO categories (id,name)
 VALUES ('3','Gifts'); 
SET IDENTITY_INSERT categories OFF 

exec sp_MsForEachTable 'sp_ConstraintState ''?'',1'
GO

DROP PROCEDURE sp_ConstraintState