<?php
/**
 * Default template for displaying a customer
 * downloads.
 *
 * 
 * @version 1.1.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/content-downloads.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div id="it-exchange-downloads">
	<?php it_exchange( 'customer', 'menu' ); ?>
	<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
		<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
			<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
				<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
					<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
						<div class="downloads-wrapper">
							<?php while ( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
								<div class="download">
									<div class="download-product">
										<a href="<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'confirmation-url' ) ); ?>" class="button">
											<?php _e( 'Transaction', 'it-l10n-ithemes-exchange' ); ?>
										</a>
									</div>
									<div class="download-info">
										<h4><?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?></h4>
										<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
											<?php if ( ! it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
												<p><?php _e( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'it-l10n-ithemes-exchange' ); ?></p>
											<?php endif; ?>
											<ul class="transaction-product-download-hashes">
											<?php while( it_exchange( 'transaction', 'product-download-hashes' ) ) : ?>
												<li class="transaction-product-download-hash">
													<code class="download-hash">
														<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'hash' ) ); ?>
													</code>
													<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'expires' ) ) ) : ?>
														<span class="download-expiration">
															<?php _e( 'Expires on', 'it-l10n-ithemes-exchange' ); ?> <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'expiration-date' ) ); ?>
														</span>
													<?php else : ?>
														<span class="download-expiration">
															<?php _e( 'No expiration date', 'it-l10n-ithemes-exchange' ); ?>
														</span>
													<?php endif; ?>
													<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) ) : ?>
														<span class="download-limit">
															<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'downloads-remaining' ) ); ?> <?php _e( 'download(s) remaining', 'it-l10n-ithemes-exchange' ); ?>
														</span>
													<?php else : ?>
														<span class="download-limit">
															<?php _e( 'Unlimited downloads', 'it-l10n-ithemes-exchange' ); ?>
														</span>
													<?php endif; ?>
													<?php if ( !it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) || it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'downloads-remaining' ) ) ) : ?>
														<?php if ( it_exchange( 'transaction', 'get-cleared-for-delivery' ) ) : ?>
															<span>
																<a href="<?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'download-url' ) ); ?>"><?php _e( 'Download Now', 'it-l10n-ithemes-exchange' ); ?></a>
															</span>
														<?php endif; ?>
													<?php endif; ?>
													</li>
												<?php endwhile; ?>
											</ul>
										<?php endif; ?>
									</div>
								</div>
							<?php endwhile; ?>
						</div>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>
