  </div> <!-- /.page -->
</main> <!-- /.content -->
</div> <!-- /.layout -->

<footer class="app-footer">
  <div class="footer-container">
    <div class="footer-left">
      <img src="<?php echo $depth; ?>assets/images/logo.png" class="footer-logo" alt="upDate CRM Logo">
      <div>
        <div class="footer-brand">upDate CRM Pro <span class="badge">v1.0</span></div>
        <div class="footer-copy">© <?php echo date('Y'); ?> upDt Education Technology Pvt. Ltd. All rights reserved.</div>
      </div>
    </div>
    <div class="footer-right">
      <span class="status-indicator"><span class="dot pulse"></span> System Online & Synchronized</span>
      <span class="footer-divider">|</span>
      <a href="<?php echo ($isAdmin ? $depth.'admin/settings.php' : $depth.'user/profile.php'); ?>" class="footer-link">System Settings</a>
      <span class="footer-divider">|</span>
      <a href="<?php echo ($isAdmin ? $depth.'admin/knowledge.php' : $depth.'user/knowledge.php'); ?>" class="footer-link">Knowledge Base</a>
    </div>
  </div>
</footer>
</body>
</html>
