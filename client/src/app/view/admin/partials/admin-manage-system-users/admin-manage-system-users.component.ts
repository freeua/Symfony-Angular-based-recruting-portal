import { Component, OnInit } from '@angular/core';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EditAdmin } from '../../../../../entities/models-admin';
import { Router } from '@angular/router';

@Component({
  selector: 'app-admin-manage-system-users',
  templateUrl: './admin-manage-system-users.component.html',
  styleUrls: ['./admin-manage-system-users.component.scss']
})
export class AdminManageSystemUsersComponent implements OnInit {

  public adminsList = Array<EditAdmin>();
  public selectedAdmin = new EditAdmin({});
  public modalActiveClose: any;
  public preloaderPage = true;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = false;

  public allowVideo = false;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _sharedService: SharedService,
    private readonly _toastr: ToastrService,
    private readonly _modalService: NgbModal,
    private readonly _router: Router
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    if (localStorage.getItem('role') === 'ROLE_ADMIN'){
      this._router.navigate(['/admin/dashboard']);
    }
    else {
      this.getAllAdmins('').then(() => {
        this.getAdminVideoStatusCandidate();
      });
    }
  }

  /**
   * Get candidate video status for admin
   * @returns {Promise<void>}
   */
  public async getAdminVideoStatusCandidate(): Promise<void> {
    try {
      const response = await this._adminService.getAdminVideoStatusCandidate();
      this.allowVideo = response.allowVideo;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update candidate video status for admin
   * @returns {Promise<void>}
   */
  public async updateAdminVideoStatusCandidate(field, value): Promise<void> {
    value = !value;
    const data = {[field]: value};

    try {
      await this._adminService.updateAdminVideoStatusCandidate(data);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.adminsList = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public async loadPagination(search): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getAllAdmins(search);
  }

  /**
   * Get all admins
   * @param search
   * @return {Promise<void>}
   */
  public async getAllAdmins(search: string): Promise<void> {
    try {
      this.adminsList = await this._adminService.getAllAdmins(search, this.pagination);

      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete admin
   * @param admin {object}
   * @param adminList {Array}
   * @return {Promise<void>}
   */
  public async deleteAdmin(admin, adminList): Promise<void> {
    try {
      await this._adminService.deleteAdmin(admin.id);
      this._toastr.success('Admin has been deleted');
      const index = adminList.indexOf(admin);
      adminList.splice(index, 1);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Managed modal
   * @param content {any}
   * @param admin {object}
   */
  public openVerticallyCentered(content: any, admin): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedAdmin = admin;
  }

  /**
   * Managed modal
   * @param content {any}
   */
  public openVerticallyCenteredCreated(content: any): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
  }
}
